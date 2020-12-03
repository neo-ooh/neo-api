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
 * Class DisableBroadSignSchedule
 * Disable a broadsign schedule, effectively stopping its broadcast
 *
 * @package Neo\Jobs
 */
class DisableBroadSignSchedule implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the schedule in Access
     */
    protected int $scheduleID;


    /**
     * Create a new job instance.
     *
     * @param int $scheduleID ID of the schedule in Access
     *
     * @return void
     */
    public function __construct (int $scheduleID) {
        $this->scheduleID = $scheduleID;
    }

    /**
     * Execute the job
     *
     * @return void
     */
    public function handle (): void {
        if(config("app.env") === "testing") {
            return;
        }

        // Get the campaign
        $schedule = Schedule::query()->findOrFail($this->scheduleID);

        if (!$schedule->broadsign_schedule_id) {
            // This schedule doesn't have a BroadSign ID, do nothing.
            return;
        }

        // Update the broadsign campaign
        $bsSchedule = BSSchedule::get($schedule->broadsign_schedule_id);
        $bsSchedule->active = false;
        $bsSchedule->weight = 0;
        $bsSchedule->save();
    }
}
