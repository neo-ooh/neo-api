<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AssociateAdCopyWithBundle.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Creatives;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignConfig;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Bundle;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;

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
     * @param BroadSignConfig $config
     * @param int             $bundleID
     * @param int             $creativeId
     */
    public function __construct(BroadSignConfig $config, int $bundleID, int $creativeId) {
        parent::__construct($config);

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

        if ($creative === null) {
            // Creative does not exist, remove it
            return;
        }

        // Here we only take the first Id as there should only be one per network.
        $externalId = $creative->getExternalId($this->config->networkID);

        if (!$externalId) {
            // No Ad-copy for this creative, try again later
            $this->release(60);
            return;
        }

        $bundle = new Bundle($this->getAPIClient(), ["id" => $this->bundleID]);

        // Try the association. If it fails, try again later.
        // Broadsign Do not allow an ad-copy to be associated with a bundle until it has finished uploading, which is done async.
        try {
            $bundle->associateCreative((int)$externalId);
        } catch (ClientException $e) {
            if ($e->getCode() === '400') {
                // The creative has not finished uploading, let's try again later.
                $this->release(120);
            }

            throw $e;
        }
    }
}
