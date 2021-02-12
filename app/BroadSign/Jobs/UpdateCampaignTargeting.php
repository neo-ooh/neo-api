<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateCampaignTargeting.php
 */

namespace Neo\BroadSign\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\BroadSign\Models\ResourceCriteria;
use Neo\Models\Campaign;
use Neo\Models\FormatLayout;

/**
 * Class CreateBroadSignCampaign
 * Create the BroadSign campaign matching the Access' one. BS Campaigns are created with a far-future end date. This is
 * to prevent rebooking, which as of now, 2020-10, cannot be done automatically. Actual end date of the campaign is
 * handled inside Direct
 *
 * @package Neo\Jobs
 */
class UpdateCampaignTargeting extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $campaignID;


    /**
     * Create a new job instance.
     *
     * @param int $campaignID ID of the campaign created on Access
     *
     * @return void
     */
    public function __construct(int $campaignID) {
        $this->campaignID = $campaignID;
    }

    /**
     * Execute the job.
     *
     * @param BroadSign $broadsign
     *
     * @return void
     */
    public function handle(): void {
        // To target campaign a campaign, we need to list all the layouts currently used by the schedules in the campaign, and sync the criterions applied to the campaign with the ones required by the schedules

        /** @var Campaign $campaign */
        $campaign = Campaign::with("schedules.content.layout")->find($this->campaignID);

        if ($campaign->broadsign_reservation_id === null) {
            // Campaign is not registered in Broadsign, retry later
            $this->release(60);
            return;
        }

        // First list all the layouts
        /** @var Collection<FormatLayout> $layouts */
        $layouts = $campaign->schedules->map(fn($schedule) => $schedule->content->layout);

        // List the types of all the layouts
        $requiredCriteria = $layouts
            ->map(fn($layout) => $layout
                ->frames
                ->map(fn($frame) => $frame->pluck("type")))
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        // Enumerate over the criteria already applied to the campaign
        $campaignCriteria = ResourceCriteria::for($campaign->broadsign_reservation_id);

        /** @var ResourceCriteria $criterion */
        foreach ($campaignCriteria as $criterion) {
            // Is this criterion in our requirements ?
            if (in_array($criterion->id, $requiredCriteria, true)) {
                // Yes, remove it from our requirements
                unset($requiredCriteria[array_search($criterion->id, $requiredCriteria, true)]);
            }

            // No, remove it from the server
            $criterion->active = false;
            $criterion->save();
        }

        // We are now left only with the criteria that needs to be added to the campaign.
        /** @var string $criterion */
        foreach ($requiredCriteria as $criterion) {
            $criterionId = null;
            $criteriontype = null;

            switch ($criterion) {
                case "MAIN":
                    $criterionId = BroadSign::getDefaults()["advertising_criteria_id"];
                    $criteriontype = 10;
                    break;
                case "RIGHT":
                    $criterionId = BroadSign::getDefaults()["right_frame_criteria_id"];
                    $criteriontype = 2;
            }

            BSCampaign::addResourceCriteria([
                "active"      => true,
                "criteria_id" => $criterionId,
                "parent_id"   => $campaign->broadsign_reservation_id,
                "type"        => $criteriontype,
            ]);
        }

        // Campaign criteria up to date
    }
}
