<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - NetworkUpdate.php
 */

namespace Neo\Console;

use Illuminate\Console\Command;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\CreateBroadSignCampaign;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\CreateBroadSignSchedule;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\SynchronizeFormats;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\SynchronizeLocations;
use Neo\BroadSign\Jobs\Creatives\Creatives\Creatives\SynchronizePlayers;
use Neo\BroadSign\Models\Bundle;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\BroadSign\Models\Schedule as BSSchedule;
use Neo\Models\Campaign;
use Neo\Models\Schedule;

class RebuildResources extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild all campaigns, schedules and bundles for Connect resources in Broadsign';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle (): int {
        // First step is to deactivate all schedules and bundles
        $schedules = Schedule::all();
        foreach ($schedules as $schedule) {
            if($schedule->broadsign_schedule_id === null) {
                continue;
            }

            $bsSchedule = BSSchedule::get($schedule->broadsign_schedule_id);
            $bsSchedule->active = false;
            $bsSchedule->weight = 0;
            $bsSchedule->save();

            $bsBundles = Bundle::bySchedule($schedule->broadsign_schedule_id);
            foreach ($bsBundles as $bsBundle) {
                $bsBundle->active = false;
                $bsBundle->save();
            }

            $schedule->broadsign_schedule_id = null;
            $schedule->broadsign_bundle_id = null;
            $schedule->save();
        }

        // Second step is to deactivate all campaigns
        $campaigns = Campaign::all();
        foreach ($campaigns as $campaign) {
            if($campaign->broadsign_reservation_id === null) {
                continue;
            }

            $bsCampaign = BSCampaign::get($campaign->broadsign_reservation_id);
            $bsCampaign->active = false;
            $bsCampaign->state = 2;
            $bsCampaign->save();

            $campaign->broadsign_reservation_id = null;
            $campaign->save();
        }

        // Now, we start by replicating all campaigns
        foreach ($campaigns as $campaign) {
            CreateBroadSignCampaign::dispatchSync($campaign->id);
        }

        // Now, we replicate all schedules
        foreach ($schedules as $schedule) {
            CreateBroadSignSchedule::dispatchSync($schedule->id, $schedule->owner_id);
        }

        // An now we pray

        return 0;
    }
}
