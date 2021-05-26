<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeLocations.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Schedules;


use GuzzleHttp\Psr7\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Creative;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\PiSignage\Jobs\Campaigns\SetCampaignSchedules;
use Neo\Services\Broadcast\PiSignage\Jobs\Creatives\AssignCreativeValidity;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Asset;
use Neo\Services\Broadcast\PiSignage\Models\Playlist;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class CreateSchedule extends PiSignageJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $scheduleId;

    public function uniqueId(): int {
        return $this->scheduleId;
    }

    public function __construct(PiSignageConfig $config, int $scheduleId) {
        parent::__construct($config);
        $this->scheduleId = $scheduleId;
    }

    public function handle(): void {
        // in PiSignage, since Schedule do not exist there, we place the creatives in the playlist only if the schedule is approved.

        /** @var Schedule $schedule */
        $schedule = Schedule::query()->find($this->scheduleId);

        if (!$schedule) {
            // schedule does not exist
            return;
        }

        $creatives = $schedule->content->creatives;

        /** @var Creative $creative */
        foreach ($creatives as $creative) {
            /** @var Asset $asset */
            $assetName = $schedule->id . "@" . $creative->id . "." . $creative->properties->extension;
            Asset::makeStatic($this->getAPIClient(), $assetName, Utils::tryFopen($creative->properties->file_url, 'r'));

            AssignCreativeValidity::dispatchSync($this->config, $creative->id, $schedule->id);
        }

        SetCampaignSchedules::dispatch($this->config, $schedule->campaign_id);
    }
}
