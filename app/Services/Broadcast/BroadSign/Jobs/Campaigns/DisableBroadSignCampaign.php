<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisableBroadSignCampaign.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Campaigns;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Campaign as BSCampaign;

/**
 * Class DisableBroadSignCampaign
 * Disable a broadsign campaign, effectively stopping the broadcast of all its schedules
 *
 * @package Neo\Jobs
 */
class DisableBroadSignCampaign extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the campaign in Access
     */
    protected int $reservationId;

    public function uniqueId(): int {
        return $this->reservationId;
    }


    /**
     * Create a new job instance.
     *
     * @param int $reservationId ID of the campaign in Access
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $reservationId) {
        parent::__construct($config);
        $this->reservationId = $reservationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        // Update the broadsign campaign
        $bsReservation = BSCampaign::get($this->getAPIClient(), $this->reservationId);

        if ($bsReservation === null) {
            // We do not throw any error on reservation not found as we were already trying to deactivate it.
            return;
        }

        $bsReservation->active = false;
        $bsReservation->state  = $bsReservation->state === 3 ? $bsReservation->state : 2;
        $bsReservation->save();
    }
}
