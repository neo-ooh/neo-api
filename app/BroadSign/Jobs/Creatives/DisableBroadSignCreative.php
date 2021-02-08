<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - DisableBroadSignCreative.php
 */

namespace Neo\BroadSign\Jobs\Creatives;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Jobs\BroadSignJob;
use Neo\BroadSign\Models\Creative as BSCreative;

/**
 * Class DisableBroadSignCreative
 *
 * @package Neo\Jobs
 *
 * Imports the specified creative in BroadSign and register its BroadSign ID.
 */
class DisableBroadSignCreative extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $adCopyID;

    /**
     * Create a new job instance.
     *
     * @param int $adCopyID ID of the ad-copy to disable
     *
     * @return void
     */
    public function __construct(int $adCopyID) {
        $this->adCopyID = $adCopyID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void {
        $bsCreative = BSCreative::get($this->adCopyID);

        if ($bsCreative === null) {
            // We do not throw any error on ad-copy not found as we were already trying to deactive it.
            return;
        }

        $bsCreative->active = false;
        $bsCreative->save();
    }
}

