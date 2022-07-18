<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RetrySchedulesJob.php
 */

namespace Neo\Jobs\Maintenance;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Services\Broadcast\Broadcast;

/**
 * This job takes all the schedules that albeit being approved and supposed to play in the future, do not have any external_id,
 * and tries to reschedule them.
 */
class RetrySchedulesJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
    }

    public function handle() {
        Schedule::query()
                ->where("end_date", ">", Carbon::now())
                ->where("is_approved", true)
                ->whereNull(["external_id_1", "external_id_2"])
                ->with("campaign")
                ->get()
                ->each(fn($schedule) => Broadcast::network($schedule->campaign->network_id)
                                                 ->createSchedule($schedule->getKey(), $schedule->owner_id));

    }
}
