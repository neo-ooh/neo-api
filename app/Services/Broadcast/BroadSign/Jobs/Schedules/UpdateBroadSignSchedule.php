<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateBroadSignSchedule.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Schedules;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignConfig;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Schedule as BSSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;

/**
 * Class UpdateBroadSignSchedule
 * Update a BroadSign schedule to reflect the changes made to its counterpart in Connectt.
 *
 * @package Neo\Jobs
 *
 * @warning This does not update the broadcasting status of the schedule, only its properties.
 * @see     UpdateBroadSignScheduleStatus
 */
class UpdateBroadSignSchedule extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the updated schedule in Connect
     */
    protected int $scheduleID;

    public function uniqueId(): int {
        return $this->scheduleID;
    }


    /**
     * Create a new job instance.
     *
     * @param int $scheduleID ID of the updated schedule in Connect
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $scheduleID) {
        parent::__construct($config);

        $this->scheduleID = $scheduleID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        /** @var \Neo\Modules\Broadcast\Models\Schedule $schedule */
        $schedule = Schedule::query()->find($this->scheduleID);

        if (!$schedule) {
            return; // Schedule does not exist
        }

        if (!$schedule->external_id_2) {
            // This schedule doesn't have a known counterpart in broadsign, create it
            CreateBroadSignSchedule::dispatch($this->config, $this->scheduleID, $schedule->owner_id);
            return;
        }

        // We need to make sure the end time is not after 23:59:00
        $endTime = $schedule->end_date;
        if ($endTime->isAfter($endTime->copy()->setTime(23, 59, 00))) {
            $endTime = $endTime->setTime(23, 59, 00);
        }

        // Get and update the schedule
        $bsSchedule             = BSSchedule::get($this->getAPIClient(), $schedule->external_id_2);
        $bsSchedule->name       = $schedule->content->name . " Schedules";
        $bsSchedule->start_date = $schedule->start_date->toDateString();
        $bsSchedule->start_time = $schedule->start_date->setSecond(0)->toTimeString();
        $bsSchedule->end_date   = $schedule->end_date->toDateString();
        $bsSchedule->end_time   = $endTime->toTimeString();
        $bsSchedule->save();

        // Check if the schedule status needs to be updated
        if ($schedule->is_approved !== $bsSchedule->active) {
            // Mismatch, trigger an update of the schedule status
            UpdateBroadSignScheduleStatus::dispatchSync($this->config, $this->scheduleID);
        }
    }
}
