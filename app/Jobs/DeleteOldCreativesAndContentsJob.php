<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteOldCreativesAndContentsJob.php
 */

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Neo\Models\Content;

class DeleteOldCreativesAndContentsJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $contentsOffset = 31 * 4; // 4 months
    protected int $schedulesOffset = 31;    // 1 month

    public function handle() {
        // Select the id of all contents that have been uploaded more than $contentOffset days ago, and whose schedules have finished at least $schedulesOffset days ago.
        $contents = Content::query()
                           ->where("created_at", "<", DB::raw("SUBDATE(NOW(), $this->contentsOffset)"))
                           ->whereRelation("schedules", function ($query) {
                               $query->where("end_date", "<", DB::raw("SUBDATE(NOW(), $this->schedulesOffset)"))
                                     ->whereNull("deleted_at");
                           })->orWhereDoesntHave("schedules")->with(["creatives.external_ids", "schedules"])
                           ->withCount(["schedules", "creatives"])
                           ->lazy();

        /** @var Content $content */
        foreach ($contents as $content) {
            dump("Deleting content #{$content->library_id}-{$content->getKey()}...");
            $content->delete();
        }
    }
}
