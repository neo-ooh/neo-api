<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeInventoriesJob.php
 */

namespace Neo\Modules\Properties\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Properties\Models\InventoryProvider;

class SynchronizeInventoriesJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
    }

    public function handle(): void {
        // List all our enabled inventories, and trigger push and pull accordingly
        $inventories = InventoryProvider::query()->where("is_active", "=", true)->get();

        /** @var InventoryProvider $inventory */
        foreach ($inventories as $inventory) {
            if ($inventory->auto_pull) {
                PullFullInventoryJob::dispatch($inventory->getKey());
            }

            if ($inventory->auto_push) {
                PushFullInventoryJob::dispatch($inventory->getKey());
            }
        }
    }
}
