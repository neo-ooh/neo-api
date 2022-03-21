<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisableBroadSignCreative.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Creatives;

use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Creative as BSCreative;

/**
 * Class DisableBroadSignCreative
 *
 * @package Neo\Jobs
 *
 * Imports the specified creative in BroadSign and register its BroadSign ID.
 */
class DisableBroadSignCreative extends BroadSignJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $adCopyID;

    public function uniqueId(): int {
        return $this->adCopyID;
    }

    /**
     * Create a new job instance.
     *
     * @param int $adCopyID ID of the ad-copy to disable
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $adCopyID) {
        parent::__construct($config);

        $this->adCopyID = $adCopyID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void {
        try {
            $bsCreative = BSCreative::get($this->getAPIClient(), $this->adCopyID);
        } catch (RequestException $e) {
            if ($e->getResponse()?->getStatusCode() === 404) {
                // The creative does not exist in BroadSign, this job is therefore useless
                return;
            }

            // Another error occurred, pass the exception untouched
            throw $e;
        }

        if ($bsCreative === null) {
            // We do not throw any error on ad-copy not found as we were already trying to deactivate it.
            return;
        }

        $bsCreative->active = false;
        $bsCreative->save();

        Log::channel("activity")->info("broadsign.creative.deactivated", [
            "external-id" => $bsCreative->id,
        ]);
    }
}

