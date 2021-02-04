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
    protected int $reservationId;


    /**
     * Create a new job instance.
     *
     * @param int $reservationId ID of the campaign in Access
     *
     * @return void
     */
    public function __construct (int $reservationId) {
        $this->reservationId = $reservationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (): void {
        if(config("app.env") !== "production") {
            return;
        }

        // Update the broadsign campaign
        $bsReservation = BSCampaign::get($this->reservationId);

        if($bsReservation === null) {
            // We do not throw any error on reservation not found as we were already trying to deactivate it.
            return;
        }

        $bsReservation->active = false;
        $bsReservation->state = 2;
        $bsReservation->save();
    }
}
