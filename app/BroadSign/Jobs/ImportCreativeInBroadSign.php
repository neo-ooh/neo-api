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
class ImportCreativeInBroadSign implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $creativeID;

    /**
     * Create a new job instance.
     *
     * @param int $creativeID ID of the creative to import
     *
     * @return void
     */
    public function __construct (int $creativeID) {
        $this->creativeID = $creativeID;
    }

    /**
     * Execute the job.
     *
     * @param BroadSign $broadsign
     *
     * @return void
     * @throws Exception
     */
    public function handle (BroadSign $broadsign): void {
        if(config("app.env") === "testing") {
            return;
        }

        /** @var Creative $creative */
        $creative = Creative::query()->findOrFail($this->creativeID);

        if ($creative->broadsign_ad_copy_id) {
            // This creative already has a BroadSign ID, do nothing.
            return;
        }

        $attributes = "width=" . $creative->frame->width . "\n";
        $attributes .= "height=" . $creative->frame->height . "\n";

        if ($creative->extension === "mp4") {
            $interval = new DateInterval("PT" . $creative->content->duration . "S");
            $attributes .= "duration=" . $interval->format("H:I:S") . "\n";
        }

        $bsCreative = new BSCreative();
        $bsCreative->attributes = $attributes;
        $bsCreative->name = $creative->owner->name . " <" . $creative->owner->email . "> - " . $creative->original_filename;
        $bsCreative->parent_id = $broadsign->getDefaults()["customer_id"];
        $bsCreative->url = $creative->file_url;
        $bsCreative->create();

        $creative->broadsign_ad_copy_id = $bsCreative->id;
        $creative->save();
    }
}

