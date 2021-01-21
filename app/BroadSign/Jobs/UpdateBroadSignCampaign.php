<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateBroadSignCampaign.php
 */

namespace Neo\BroadSign\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Models\Campaign as BSCampaign;
use Neo\Models\Campaign;

/**
 * Class UpdateBroadSignCampaign
 * We update the broadsign campaign to reflect the changes made on direct
 *
 * @package Neo\Jobs
 */
class UpdateBroadSignCampaign implements ShouldQueue {
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
     * @noinspection PhpUnusedParameterInspection
     */
    public function handle (BroadSign $broadsign): void {
        if(config("app.env") === "testing") {
            return;
        }

        // Get the Access and Broadsign campaign
        /** @var Campaign $campaign */
        $campaign   = Campaign::query()->findOrFail($this->campaignID);
        $bsCampaign = BSCampaign::get($campaign->broadsign_reservation_id);

        // Update the name of the campaign
        $bsCampaign->name = $campaign->name;
        $bsCampaign->save();

        // Update the campaign saturation as needed
        $bsCampaign->saturation = $campaign->loop_saturation > 0 ? $campaign->loop_saturation : $campaign->schedules->filter(fn($schedule) => $schedule->is_approved)->count();

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
            $bsCampaign->addLocations($missingLocations);
        }

        // Is there any broadsign location that needs to be removed from the campaign ?
        $locationsToRemove = $bsLocationsID->diff($locationsID);
        if ($locationsToRemove->count() > 0) {
            $bsCampaign->removeLocations($locationsToRemove);
        }
        // Campaigns is good
    }
}
