<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReuploadCreativeToBroadSignCommand.php
 */

namespace Neo\Console\Commands;

use Illuminate\Console\Command;
use Neo\Models\Content;
use Neo\Models\Schedule;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\Creatives\DisableBroadSignCreative;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\CreateBroadSignSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\DisableBroadSignSchedule;

class ReuploadCreativeToBroadSignCommand extends Command {
    protected $signature = 'content:re-upload {content}';

    protected $description = 'Command description';

    public function handle() {
        $contentId = $this->argument("content");
        /** @var Content $content */
        $content = Content::query()
                          ->with(["creatives", "creatives.external_ids", "schedules", "schedules.campaign"])
                          ->find($contentId);

        // Disable the schedules
        /** @var Schedule $schedule */
        foreach ($content->schedules as $schedule) {
            $config = Broadcast::network($schedule->campaign->network_id)->getConfig();

            if (!($config instanceof BroadSignConfig) || $schedule->external_id_1 === null || $schedule->external_id_2 === null) {
                continue;
            }

            $this->info("Disabling schedule #$schedule->id...");

            DisableBroadSignSchedule::dispatchSync($config, $schedule->external_id_2);
            $schedule->external_id_2 = null;
            $schedule->external_id_1 = null;
            $schedule->save();

            /** @var Creative $creative */
            foreach ($content->creatives as $creative) {
                if ($externalId = $creative->getExternalId($config->networkID)) {
                    $this->info("Disabling creative #$creative->id...");

                    DisableBroadSignCreative::dispatchSync($config, $externalId);
                    $creative->external_ids()->where("network_id", "=", $config->networkID)->delete();
                }
            }
        }

        $this->info("Reloading...");
        $content->creatives->each(fn(Creative $creative) => $creative->refresh());

        // Re-do the schedule, which will trigger a reupload of the creatives as well
        /** @var Schedule $schedule */
        foreach ($content->schedules as $schedule) {
            if ($schedule->trashed()) {
                continue;
            }

            $config = Broadcast::network($schedule->campaign->network_id)->getConfig();

            if (!($config instanceof BroadSignConfig)) {
                continue;
            }

            $this->info("Create new schedule #$schedule->id...");
            CreateBroadSignSchedule::dispatchSync($config, $schedule->id, $schedule->owner_id);
        }
    }
}
