<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisableExpiredSchedulesJob.php
 */

namespace Neo\Jobs\Schedules;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\Broadcast;
use Symfony\Component\Console\Output\ConsoleOutput;

class DisableExpiredSchedulesJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
    }

    public function handle() {
        // We want all schedules that are expired (end_date is passed) and who have an external id
        $schedules = Schedule::query()
                             ->whereNotNull("external_id_1")
                             ->where(function (Builder $query) {
                                 $query->where("end_date", "<", Carbon::now()->subDay())
                                       ->orWhereNotNull("deleted_at");
                             })
                             ->withoutEagerLoads()
                             ->with(["campaign"])
                             ->withTrashed()
                             ->get();

        $output = new ConsoleOutput();

        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            if (!($schedule->campaign->network_id)) {
                // ignore

                $schedule->external_id_1 = null;
                $schedule->external_id_2 = null;
                $schedule->save();
                continue;
            }

            $output->writeln("Disabling Schedule #$schedule->id");

            $broadcaster = Broadcast::network($schedule->campaign->network_id);
            $broadcaster->disableSchedule($schedule->getKey());

            // Erase external_ids
            $schedule->external_id_1 = null;
            $schedule->external_id_2 = null;
            $schedule->save();

            break;
        }
    }
}
