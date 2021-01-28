<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - CreateBroadSignCampaign.php
 */

namespace Neo\BroadSign\Jobs;

use Carbon\Carbon as Date;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Models\Bundle;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\BroadSign\Models\Schedule as BSSchedule;
use Neo\Models\Campaign;
use Neo\Models\Frame;
use Neo\Models\Schedule;

/**
 * Class CreateBroadSignCampaign
 * Create the BroadSign campaign matching the Access' one. BS Campaigns are created with a far-future end date. This is
 * to prevent rebooking, which as of now, 2020-10, cannot be done automatically. Actual end date of the campaign is
 * handled inside Direct
 *
 * @package Neo\Jobs
 */
class RebuildResources implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct () {
        $this->delay(60);
    }

    /**
     * Execute the job.
     *
     * @param BroadSign $broadsign
     *
     * @return void
     */
    public function handle (BroadSign $broadsign): void {
        if(config("app.env") === "testing") {
            return;
        }

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

            $schedule->broadsign_schedule_id = null;
            $schedule->save();

            if($schedule->broadsign_bundle_id === null) {
                continue;
            }

            $bsBundle = Bundle::bySchedule($schedule->broadsign_bundle_id);
            $bsBundle->active = false;
            $bsBundle->save();

            $schedule->broadsign_bundle_id = null;
            $schedule->save();
        }

        // Second step is to deactivate all campaigns
        $campaigns = Campaign::all();
        foreach ($campaigns as $campaign) {
            if($campaign->broadsign_reservation_id === null) {
                return;
            }

            $bsCampaign = BSCampaign::get($campaign->broadsign_reservation_id);
            $bsCampaign->active = false;
            $bsCampaign->state = 2;
            $bsCampaign->save();

            $campaign->broadsign_reservation_id = null;
            $bsCampaign->save();
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
    }
}
