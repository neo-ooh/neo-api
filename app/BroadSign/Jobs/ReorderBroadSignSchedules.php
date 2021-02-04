<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ReorderBroadSignSchedules.php
 */

namespace Neo\BroadSign\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Bundle;
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
class ReorderBroadSignSchedules implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int Id of the campaign whose schedules needs to be updated
     */
    protected int $campaignId;


    /**
     * Create a new job instance.
     *
     * @param int $campaignId
     *
     * @return void
     */
    public function __construct(int $campaignId) {
        $this->campaignId = $campaignId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        if (config("app.env") !== "production") {
            return;
        }

        // Get all the schedules of the campaign
        $schedules = Schedule::query()
                             ->where("id", "=", $this->campaignId)
                             ->where("broadsign_schedule_id", "<>", null)->get();

        // For each schedule, we need to retrieve its bundle, and update its position.
        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $bundles = Bundle::bySchedule($schedule->id);

            if(count($bundles) === 0) {
                return;
            }

            /** @var Bundle $bundle */
            $bundle = $bundles[0];
            $bundle->position = $schedule->order;
            $bundle->save();
        }

    }
}
