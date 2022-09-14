<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TargetCampaign.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Campaigns;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Neo\Models\BroadSignCriteria;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Campaign as BSCampaign;
use Neo\Services\Broadcast\BroadSign\Models\ResourceCriteria;

/**
 * Class TargetCampaign
 * Campaign targeting in BroadSign is made of two stones : The Resource Criteria and the Skin Slots, which map to the layout used
 * in the campaign and the locations it broadcasts to.
 *
 * @package Neo\Jobs
 */
class TargetCampaign extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $campaignID;

    public function uniqueId(): int {
        return $this->campaignID;
    }


    /**
     * Create a new job instance.
     *
     * @param int $campaignID ID of the campaign created on Connect
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $campaignID) {
        parent::__construct($config);

        $this->campaignID = $campaignID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        // To target a campaign, We only need to apply the advertising criteria. Then, each locations where the campaign broadcasts will be targeted with the appropriate frames.

        /** @var Campaign $campaign */
        $campaign = Campaign::with("schedules.content.layout")->find($this->campaignID);

        if ($campaign->external_id === null) {
            // Campaign is not registered in Broadsign, retry later
            $this->release(60);
            return;
        }

        // Get the BroadSign Campaign
        $bsCampaign = BSCampaign::get($this->getAPIClient(), $campaign->external_id);

        // List the frames targeted by the campaign
        $targetedFrames    = $campaign->format
            ->layouts
            ->pluck("frames")
            ->flatten()
            ->unique("id")
            ->values();
        $requestedCriteria = $targetedFrames->pluck("settings_broadsign")->pluck("criteria")->unique("id")->values();

        // Make sure the campaign has the proper criteria applied to it
        $this->validateCampaignCriteria($campaign, $requestedCriteria);

        // Get the campaign locations
        $locations   = $campaign->locations;
        $locationsID = $locations->pluck("external_id");

        // Get the broadsign campaign locations (display units)
        $bsLocations   = $bsCampaign->locations();
        $bsLocationsID = $bsLocations->map(fn($bsloc) => $bsloc->id);

        // Is there any broadsign location missing from the campaign ?
        $missingLocations = $locationsID->diff($bsLocationsID);
        if ($missingLocations->isNotEmpty()) {
            // Associate missing locations
            $bsCampaign->addLocations($missingLocations, $requestedCriteria->pluck("broadsign_criteria_id"));
        }

        // Is there any broadsign location that needs to be removed from the campaign ?
        $locationsToRemove = $bsLocationsID->diff($locationsID);
        if ($locationsToRemove->isNotEmpty()) {
            $bsCampaign->removeLocations($locationsToRemove);
        }

        // Campaigns is good
    }

    protected function validateCampaignCriteria(Campaign $campaign, Collection $requestedCriteria): void {
        // Enumerate over the criteria already applied to the campaign
        $campaignCriteria = ResourceCriteria::for($this->getAPIClient(), $campaign->external_id);

        /** @var ResourceCriteria $criterion */
        foreach ($campaignCriteria as $criterion) {
            // Is this criterion in our requirements ?
            if (in_array($criterion->id, $requestedCriteria->pluck("broadsign_criteria_id")->toArray(), true)) {
                // Yes, remove it from our requirements
                $requestedCriteria = $requestedCriteria->filter(fn($criteria) => $criteria->broadsign_criteria_id !== $criterion->id);
                continue;
            }

            // No, remove it from the server
            $criterion->active = false;
            $criterion->save();
        }

        // We are now left only with the criteria that needs to be added to the campaign.
        /** @var BroadSignCriteria $criterion */
        foreach ($requestedCriteria as $criterion) {
            BSCampaign::addResourceCriteria($this->getAPIClient(), [
                "active"      => true,
                "criteria_id" => $criterion->broadsign_criteria_id,
                "parent_id"   => $campaign->external_id,
                "type"        => 8,
            ]);
        }
    }
}
