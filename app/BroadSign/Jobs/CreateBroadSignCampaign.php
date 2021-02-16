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

use Illuminate\Bus\Queueable;
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
class CreateBroadSignCampaign extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $campaignID;

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
        // Get the access campaign
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->find($this->campaignID);

        if ($campaign === null || $campaign->broadsign_reservation_id) {
            // this campaign either doesn't exist or already has a reservation ID, do nothing.
            return;
        }

        // Prepare the start and end date
        $startDate = $campaign->start_date->setTime(0, 0);
        $endDate = $startDate->copy()
                             ->addYears(BroadSign::getDefaults()['campaign_length'])
                             ->setTime(23, 59);

        // Create the campaign
        $bsCampaign = new BSCampaign();
        $bsCampaign->auto_synchronize_bundles = true;
        $bsCampaign->domain_id = $broadsign->getDefaults()["domain_id"];
        $bsCampaign->duration_msec = $campaign->display_duration * 1000;
        $bsCampaign->end_date = $endDate->toDateString();
        $bsCampaign->end_time = "23:59:00";
        $bsCampaign->name = $campaign->owner->name . " - " . $campaign->name;
        $bsCampaign->parent_id = $broadsign->getDefaults()["customer_id"];
        $bsCampaign->start_date = $startDate->toDateString();
        $bsCampaign->start_time = "00:00:00";
        $bsCampaign->saturation = $campaign->loop_saturation;
        $bsCampaign->default_fullscreen = false;
        $bsCampaign->create();

        // Save the BroadSign campaign ID with the Access campaign
        $campaign->broadsign_reservation_id = $bsCampaign->id;
        $campaign->save();

        // Now set the targeting of the campaign
        UpdateCampaignTargeting::dispatch($campaign->id);
    }
}
