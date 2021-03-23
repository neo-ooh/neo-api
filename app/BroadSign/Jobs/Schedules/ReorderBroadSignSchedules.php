<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReorderBroadSignSchedules.php
 */

namespace Neo\BroadSign\Jobs\Schedules;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Jobs\BroadSignJob;
use Neo\BroadSign\Models\Bundle;
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
class ReorderBroadSignSchedules extends BroadSignJob {
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
        // Get all the schedules of the campaign
        $schedules = Schedule::query()
                             ->where("id", "=", $this->campaignId)
                             ->whereNotNull("broadsign_schedule_id")->get();

        // For each schedule, we need to retrieve its bundle, and update its position.
        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $bundles = Bundle::bySchedule($schedule->id);

            if (count($bundles) === 0) {
                return;
            }

            /** @var Bundle $bundle */
            $bundle           = $bundles[0];
            $bundle->position = $schedule->order;
            $bundle->save();
        }

    }
}
