<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Schedule as BSSchedule;
use Neo\Models\Schedule;

/**
 * Class UpdateBroadSignSchedule
 * Update a BroadSign schedule to reflect the changes made to its counterpart in Access.
 *
 * @package Neo\Jobs
 *
 * @warning This does not update the broadcasting status of the schedule, only its properties.
 * @see     UpdateBroadSignScheduleStatus
 */
class UpdateBroadSignSchedule implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the updated schedule in Access
     */
    protected int $scheduleID;


    /**
     * Create a new job instance.
     *
     * @param int $scheduleID ID of the updated schedule in Access
     *
     * @return void
     */
    public function __construct (int $scheduleID) {
        $this->scheduleID = $scheduleID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (): void {
        if(config("app.env") === "testing") {
            return;
        }

        $schedule = Schedule::query()->find($this->scheduleID);

        if (!$schedule->broadsign_schedule_id) {
            // This schedule doesn't have a BroadSign ID, do nothing.
            return;
        }

        // Get and update the schedule
        $bsSchedule = BSSchedule::get($schedule->broadsign_schedule_id);
        $bsSchedule->name = $schedule->content->name . " Schedules";
        $bsSchedule->start_date = $schedule->start_date->toDateString();
        $bsSchedule->start_time = $schedule->start_date->toTimeString();
        $bsSchedule->end_date = $schedule->end_date->toDateString();
        $bsSchedule->end_time = $schedule->end_date->toTimeString();
        $bsSchedule->save();
    }
}
