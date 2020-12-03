<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Jobs;

use Carbon\Carbon as Date;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\Models\Campaign;

/**
 * Class CreateBroadSignCampaign
 * Create the BroadSign campaign matching the Access' one. BS Campaigns are created with a far-future end date. This is
 * to prevent rebooking, which as of now, 2020-10, cannot be done automatically. Actual end date of the campaign is
 * handled inside Direct
 *
 * @package Neo\Jobs
 */
class CreateBroadSignCampaign implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $campaignID;


    /**
     * Create a new job instance.
     *
     * @param int $campaignID ID of the campaign created on Access
     *
     * @return void
     */
    public function __construct (int $campaignID) {
        $this->campaignID = $campaignID;
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

        // Get the access campaign
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->findOrFail($this->campaignID);

        if ($campaign->broadsign_reservation_id) {
            // this campaign already has a reservation ID, do nothing.
            return;
        }

        // Prepare the start and end date
        $startDate = Date::now();
        $endDate = $startDate->copy()->addYears($broadsign->getDefaults()['campaign_length']);

        // Create the campaign
        $bsCampaign = new BSCampaign();
        $bsCampaign->auto_synchronize_bundles = true;
        $bsCampaign->domain_id = $broadsign->getDefaults()["domain_id"];
        $bsCampaign->duration_msec = $campaign->display_duration * 1000;
        $bsCampaign->end_date = $endDate->toDateString();
        $bsCampaign->end_time = "23:59:00";
        $bsCampaign->name = $campaign->name . "(" . $campaign->owner->name . ")";
        $bsCampaign->parent_id = $broadsign->getDefaults()["customer_id"];
        $bsCampaign->start_date = $startDate->toDateString();
        $bsCampaign->start_time = "00:00:00";
        $bsCampaign->create();

        // Add the advertising criteria to the campaign
        $bsCampaign->addCriteria($broadsign->getDefaults()["advertising_criteria_id"], 8);

        // Save the BroadSign campaign ID with the Access campaign
        $campaign->broadsign_reservation_id = $bsCampaign->id;
        $campaign->save();
    }
}
