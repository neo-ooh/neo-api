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
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\Models\Campaign;
use Neo\Models\Frame;

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
        if(config("app.env") === "testing") {
            return;
        }

        // Get the access campaign
        /** @var Campaign $campaign */
        $campaign = Campaign::query()->find($this->campaignID);

        if ($campaign === null || $campaign->broadsign_reservation_id) {
            // this campaign either doesn't exist or already has a reservation ID, do nothing.
            return;
        }

        // Prepare the start and end date
        $startDate = $campaign->start_date->setTime(0, 0, 0);
        $endDate = $startDate->copy()
                             ->addYears(BroadSign::getDefaults()['campaign_length'])
                             ->setTime(23, 59, 0);

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
        $bsCampaign->default_fullscreen = $campaign->format->is_fullscreen;
        $bsCampaign->create();

        // Target the campaign. Apply criteria to tell broadsign how to play this campaign.
        $this->targetCampaign($bsCampaign, $campaign, $broadsign);

        // Save the BroadSign campaign ID with the Access campaign
        $campaign->broadsign_reservation_id = $bsCampaign->id;
        $campaign->save();

        // Trigger an update to link the locations
        UpdateBroadSignCampaign::dispatch($campaign->id);
    }

    /**
     * Apply the appropriate broadcasting criteria to the campaign
     * @param BSCampaign $bsCampaign
     * @param Campaign   $campaign
     * @param BroadSign  $broadsign
     */
    protected function targetCampaign(BSCampaign $bsCampaign, Campaign $campaign, BroadSign $broadsign): void {
        // First apply the advertising criteria, this is mandatory for every campaign.
        $bsCampaign->addCriteria(BroadSign::getDefaults()["advertising_criteria_id"], 8);

        // Check the number of frames of the format. If only one, and its a main frame, then we are done.
        if($campaign->format->frames_count === 1 && $campaign->format->frames[0]->type === Frame::TYPE_MAIN) {
            return;
        }


        // There is multiple frames for this format, we will have to give additional criteria to the campaign
        /** @var Frame $frame */
        foreach ($campaign->format->frames as $frame) {
            // Apply the appropriate criteria based on the frame type
            if($frame->type === Frame::TYPE_MAIN) {
                // Main frames are already targeted by the advertising criteria
                continue;
            }

            if($frame->type === Frame::TYPE_RIGHT) {
                $bsCampaign->addCriteria($broadsign->getDefaults()["left_frame_criteria_id"], 8);
            }
        }
    }
}
