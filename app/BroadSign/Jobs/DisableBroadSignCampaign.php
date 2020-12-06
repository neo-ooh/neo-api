<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - DisableBroadSignCampaign.php
 */

namespace Neo\BroadSign\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\Models\Campaign;

/**
 * Class DisableBroadSignCampaign
 * Disable a broadsign campaign, effectively stopping the broadcast of all its schedules
 *
 * @package Neo\Jobs
 */
class DisableBroadSignCampaign implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the campaign in Access
     */
    protected int $campaignID;


    /**
     * Create a new job instance.
     *
     * @param int $campaignID ID of the campaign in Access
     *
     * @return void
     */
    public function __construct (int $campaignID) {
        $this->campaignID = $campaignID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (): void {
        if(config("app.env") === "testing") {
            return;
        }

        // Get the campaign
        $campaign = Campaign::query()->findOrFail($this->campaignID);

        if (!$campaign->broadsign_reservation_id) {
            // This campaign doesn't have a BroadSign ID, do nothing.
            return;
        }

        // Update the broadsign campaign
        $bsSchedule = BSCampaign::get($campaign->broadsign_reservation_id);
        $bsSchedule->active = false;
        $bsSchedule->state = 2;
        $bsSchedule->save();
    }
}
