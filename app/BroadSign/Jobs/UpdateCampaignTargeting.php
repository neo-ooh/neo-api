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
use Illuminate\Support\Arr;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\BroadSign\Models\ResourceCriteria;
use Neo\Models\Campaign;
use Neo\Models\FormatLayout;

/**
 * Class UpdateCampaignTargeting
 * Campaign targeting in BroadSign is made of two stones : The Resource Criteria and the Skin Slots, whick map to the layout used in the campaign and the locations it broadcasts to.
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
     * @return void
     */
    public function handle(): void {
        // To target campaign a campaign, We only need to apply the advertising criteria. Then, each locations where the campaign broadcasts will be targeted with the appropriate frames.

        /** @var Campaign $campaign */
        $campaign = Campaign::with("schedules.content.layout")->find($this->campaignID);

        if ($campaign->broadsign_reservation_id === null) {
            // Campaign is not registered in Broadsign, retry later
            $this->release(60);
            return;
        }

        // Get the BroadSign Campaign
        $bsCampaign = BSCampaign::get($campaign->broadsign_reservation_id);

        // List the frames targeted by the campaign
        $targetedFramesTypes = $campaign->targeted_broadsign_frames;

        // Remove the advertising frame from the list of frames.
        if (($key = array_search("MAIN", $targetedFramesTypes->toArray(), true)) !== false) {
            $targetedFramesTypes->pull($key);
        }

        $frameTypesIdsMapping = [
            "LEFT" => BroadSign::getDefaults()["left_frame_criteria_id"],
            "RIGHT" => BroadSign::getDefaults()["right_frame_criteria_id"],
        ];

        // Map the types
        $targetedFramesIds = $targetedFramesTypes->map(fn($type) => $frameTypesIdsMapping[$type]);

        // Get the campaign locations
        $locations = $campaign->locations;
        $locationsID = $locations->pluck("broadsign_display_unit");

        // Get the broadsign campaign locations (display units)
        $bsLocations = $bsCampaign->locations();
        $bsLocationsID = $bsLocations->map(fn ($bsloc) => $bsloc->id);

        // Is there any broadsign location missing from the campaign ?
        $missingLocations = $locationsID->diff($bsLocationsID);
        if ($missingLocations->count() > 0) {
            // Associate missing locations
            $bsCampaign->addLocations($missingLocations, $targetedFramesIds);
        }

        // Is there any broadsign location that needs to be removed from the campaign ?
        $locationsToRemove = $bsLocationsID->diff($locationsID);
        if ($locationsToRemove->count() > 0) {
            $bsCampaign->removeLocations($locationsToRemove);
        }

        // Campaigns is good
    }
}
