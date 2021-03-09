<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - AssociateAdCopyWithBundle.php
 */

namespace Neo\BroadSign\Jobs\Creatives;

use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Jobs\BroadSignJob;
use Neo\BroadSign\Models\Bundle;
use Neo\Models\Creative;

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
    public int $backoff = 120;

    protected int $bundleID, $creativeId;

    /**
     * Create a new job instance.
     *
     * @param int $bundleID
     * @param int $creativeId
     */
    public function __construct(int $bundleID, int $creativeId) {
        $this->delay = 60;

        $this->bundleID   = $bundleID;
        $this->creativeId = $creativeId;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws
     */
    public function handle(): void {
        /** @var ?Creative $creative */
        $creative = Creative::query()->find($this->creativeId);

        if($creative === null) {
            // Creative does not exist, remove it
            return;
        }

        if($creative->broadsign_ad_copy_id === null) {
            // No Ad-copy id, try again late
            $this->release(60);
            return;
        }

        $bundle = new Bundle(["id" => $this->bundleID]);

        // Try the association. If it fails, try again later.
        // Broadsign Do not allow an ad-copy to be associated with a bundle until it has finished uploading, which is done async.
        try {
            $bundle->associateCreative($creative->broadsign_ad_copy_id);
        } catch (BadResponse $exception) {
            $this->release(120);
        }
    }
}
