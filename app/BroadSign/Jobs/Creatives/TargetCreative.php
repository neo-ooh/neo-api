<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - TargetCreative.php
 */

namespace Neo\BroadSign\Jobs\Creatives;

use Exception;
use Facade\FlareClient\Http\Exceptions\BadResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Jobs\BroadSignJob;
use Neo\BroadSign\Models\Creative as BSCreative;
use Neo\Models\Creative;

/**
 * Class ImportCreative
 *
 * @package Neo\Jobs
 *
 * Imports the specified creative in BroadSign and register its BroadSign ID.
 */
class TargetCreative extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeID;

    /**
     * Create a new job instance.
     *
     * @param int $creativeID ID of the creative to import
     *
     * @return void
     */
    public function __construct(int $creativeID) {
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

        if ($creative->broadsign_ad_copy_id === null) {
            // This creative doesn't have a Broadsign counterpart, cannot target
            return;
        }

        // We need to target the creative base on its format and frames
        if ($creative->frame->layout->frames()->count() === 1) {
            // Only one frame in the format, do nothing
            return;
        }

        $bsCreative = new BSCreative(["id" => $creative->broadsign_ad_copy_id]);

        try {
            switch ($creative->frame->type) {
                case "MAIN":
                case "LEFT":
                    $bsCreative->addCriteria(BroadSign::getDefaults()['left_frame_criteria_id'], 4);
                    break;
                case "RIGHT":
                    $bsCreative->addCriteria(BroadSign::getDefaults()['right_frame_criteria_id'], 4);
                    break;
                case "TEST_LEFT":
                    $bsCreative->addCriteria(BroadSign::getDefaults()['test_left_frame_criteria_id'], 4);
                    break;
                case "TEST_RIGHT":
                    $bsCreative->addCriteria(BroadSign::getDefaults()['test_right_frame_criteria_id'], 4);
                    break;
            }
        } catch (BadResponse $exception) {
            // Creative could not be targeted. It is most probably still uploading
            $this->release(60);
        }
    }
}

