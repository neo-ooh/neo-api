<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TargetCreative.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Creatives;

use Exception;
use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Creative;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Creative as BSCreative;

/**
 * Class ImportCreative
 *
 * @package Neo\Jobs
 *
 * Imports the specified creative in BroadSign and register its BroadSign ID.
 */
class TargetCreative extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeID;

    public function uniqueId(): int {
        return $this->creativeID;
    }

    /**
     * Create a new job instance.
     *
     * @param int $creativeID ID of the creative to import
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $creativeID) {
        parent::__construct($config);

        $this->creativeID = $creativeID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void {
        /** @var Creative $creative */
        $creative = Creative::query()->findOrFail($this->creativeID);

        $externalId = $creative->getExternalId($this->config->networkID);

        if ($externalId === null) {
            // This creative doesn't have a Broadsign counterpart, cannot target
            return;
        }

        // We need to target the creative base on its format and frames

        $bsCreative = new BSCreative($this->getAPIClient(), ["id" => (int)$externalId]);

        $criteria_id = $creative->frame->settings_broadsign?->criteria_id;

        if (!$criteria_id) {
            // All done
            return;
        }

        // Add the frame criteria
        try {
            $bsCreative->addCriteria($criteria_id, 0);
        } catch (BadResponse $exception) {
            // Creative could not be targeted. It is most probably still uploading
            $this->release(60);
        }
    }
}

