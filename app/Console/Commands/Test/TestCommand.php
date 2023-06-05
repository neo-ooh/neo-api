<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Models\BroadcastJob;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     * @return void
     */
    public function handle() {
        $jobs = BroadcastJob::query()->where(function (Builder $query) {
            $query->where("status", "=", BroadcastJobStatus::Pending)
                  ->orWhere("status", "=", BroadcastJobStatus::PendingRetry);
        })->where("scheduled_at", "<=", Carbon::now())
                            ->get();

        dump($jobs->count());
    }
}
