<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportCreativeInBroadSign.php
 */

namespace Neo\BroadSign\Jobs\Creatives;

use DateInterval;
use Exception;
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
class ImportCreativeInBroadSign extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeID;
    protected string $creativeName;

    /**
     * Create a new job instance.
     *
     * @param int $creativeID ID of the creative to import
     */
    public function __construct(int $creativeID) {
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
    public function handle(BroadSign $broadsign): void {
        /** @var Creative $creative */
        $creative = Creative::query()->findOrFail($this->creativeID);

        if ($creative->broadsign_ad_copy_id) {
            // This creative already has a BroadSign ID, do nothing.
            return;
        }

        // Depending on the creative type, we performed different operations
        switch ($creative->type) {
            case Creative::TYPE_STATIC:
                $this->importStaticCreative($creative, $broadsign);
                break;
            case Creative::TYPE_DYNAMIC:
                $this->importDynamicCreative($creative, $broadsign);
                break;
        }

        // Schedule job to target the creative accordingly
        TargetCreative::dispatch($creative->id);
    }

    protected function importStaticCreative(Creative $creative, BroadSign $broadsign): void {
        $attributes = "width={$creative->frame->width}\n";
        $attributes .= "height={$creative->frame->height}\n";

        if ($creative->properties->extension === "mp4") {
            $interval   = new DateInterval("PT" . $creative->content->duration . "S");
            $attributes .= "duration={$interval->format("H:I:S")}\n";
        }

        $bsCreative             = new BSCreative();
        $bsCreative->attributes = $attributes;
        $bsCreative->name       = $creative->owner->email . " - " . $creative->original_name;
        $bsCreative->parent_id  = $broadsign->getDefaults()["customer_id"];
        $bsCreative->url        = $creative->file_url;
        $bsCreative->import();

        $creative->broadsign_ad_copy_id = $bsCreative->id;
        $creative->save();
    }

    protected function importDynamicCreative(Creative $creative, BroadSign $broadsign): void {
        $attributes = "expire_on_empty_remote_dir=false\n";                        // Do not expire if connection lost
        $attributes .= "io_strategy=esf\n";                                        // ???
        $attributes .= "source={$creative->properties->url}\n";                    // URL to the resource
        $attributes .= "source_append_id=false\n";                                 // Append player ID to url (no)
        $attributes .= "source_expiry=0\n";                                        // Not sure
        $attributes .= "source_refresh={$creative->properties->refresh_interval}"; // URL refresh interval (minutes)

        $bsCreative             = new BSCreative();
        $bsCreative->attributes = $attributes;
        $bsCreative->name       = $creative->owner->email . " - " . $creative->original_name;
        $bsCreative->parent_id  = $broadsign->getDefaults()["customer_id"];
        $bsCreative->size       = -1;
        $bsCreative->create();

        $creative->broadsign_ad_copy_id = $bsCreative->id;
        $creative->save();
    }
}

