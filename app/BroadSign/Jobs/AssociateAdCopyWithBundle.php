<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - AssociateAdCopyWithBundle.php
 */

namespace Neo\BroadSign\Jobs;

use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Bundle;

/**
 * Class AssociateAdCopyWithBundle
 * Tries to associate the specified Ad-Copy with the specified Bundle. Reason of failure lies in the fact that
 * BroadSign refuses to associate an Ad-Copy that hasn't finished importing with a bundle. We then tries to associate
 * them regularly hitherto it is accepted by BroadSign.
 *
 * @package Neo\Jobs
 */
class AssociateAdCopyWithBundle extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public int $backoff = 60;

    protected int $bundleID, $adCopyID;

    /**
     * Create a new job instance.
     *
     * @param int $bundleID
     * @param int $adCopyID
     */
    public function __construct(int $bundleID, int $adCopyID) {
        $this->delay = 60;

        $this->bundleID = $bundleID;
        $this->adCopyID = $adCopyID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws BadResponse
     */
    public function handle(): void {
        $bundle = new Bundle(["id" => $this->bundleID]);
        $bundle->associateCreative($this->adCopyID);
    }
}
