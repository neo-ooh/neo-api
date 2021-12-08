<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RemoveUnusedCreativesFromBroadcasterJob.php
 */

namespace Neo\Jobs\Creatives;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Creative;
use Neo\Models\CreativeExternalId;
use Neo\Services\Broadcast\Broadcast;

class RemoveUnusedCreativesFromBroadcasterJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
    }

    public function handle() {
        // Select all creatives that have not been scheduled for the past 30 days
        $creatives = Creative::query()
                             ->with(["content.schedules", "external_ids"])
                             ->whereDoesntHave("content.schedules", function (Builder $query) {
                                 $query->where("end_date", ">", Carbon::now()->subMonth()->toDateString());
                             })->lazy(100);

        /** @var Creative $creative */
        foreach ($creatives as $creative) {
            /** @var CreativeExternalId $external_id */
            foreach ($creative->external_ids as $external_id) {
                Broadcast::network($external_id->network_id)->destroyCreative($external_id);
                $external_id->delete();
            }
        }
    }
}
