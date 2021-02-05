<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ImportCreativeInBroadSign.php
 */

namespace Neo\BroadSign\Jobs;

use DateInterval;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Models\Creative as BSCreative;
use Neo\Models\Creative;

/**
 * Class ImportCreative
 *
 * @package Neo\Jobs
 *
 * Imports the specified creative in BroadSign and register its BroadSign ID.
 */
class ImportCreativeInBroadSign extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeID;
    protected string $creativeName;

    /**
     * Create a new job instance.
     *
     * @param int $creativeID ID of the creative to import
     *
     * @return void
     */
    public function __construct(int $creativeID, string $creativeName) {
        $this->creativeID   = $creativeID;
        $this->creativeName = $creativeName;
    }

    /**
     * Execute the job.
     *
     * @param BroadSign $broadsign
     *
     * @return void
     * @throws Exception
     */
    public function handle(BroadSign $broadsign): void {
        /** @var Creative $creative */
        $creative = Creative::query()->findOrFail($this->creativeID);

        if ($creative->broadsign_ad_copy_id) {
            // This creative already has a BroadSign ID, do nothing.
            return;
        }

        $attributes = "width=" . $creative->frame->width . "\n";
        $attributes .= "height=" . $creative->frame->height . "\n";

        if ($creative->extension === "mp4") {
            $interval   = new DateInterval("PT" . $creative->content->duration . "S");
            $attributes .= "duration=" . $interval->format("H:I:S") . "\n";
        }

        $bsCreative             = new BSCreative();
        $bsCreative->attributes = $attributes;
        $bsCreative->name       = $creative->owner->email . " - " . $this->creativeName;
        $bsCreative->parent_id  = $broadsign->getDefaults()["customer_id"];
        $bsCreative->url        = $creative->file_url;
        $bsCreative->create();

        $creative->broadsign_ad_copy_id = $bsCreative->id;
        $creative->save();

        $this->targetCreative($bsCreative, $creative, $broadsign);
    }

    /**
     * Appropriately set the criteria for the ad-copy to broadcast
     * @param BSCreative $bsCreative
     * @param Creative   $creative
     * @param BroadSign  $broadsign
     */
    public function targetCreative(BSCreative $bsCreative, Creative $creative, BroadSign $broadsign) {
        // We need to target the creative base on its format and frames
        if ($creative->frame->layout->frames_count === 1) {
            // Only one frame in the format, do nothing
            return;
        }

        switch ($creative->frame->type) {
            case "MAIN":
                $bsCreative->addCriteria(BroadSign::getDefaults()['left_frame_criteria_id'], 4);
                break;
            case "RIGHT":
                $bsCreative->addCriteria(BroadSign::getDefaults()['right_frame_criteria_id'], 4);
                break;
        }
    }
}

