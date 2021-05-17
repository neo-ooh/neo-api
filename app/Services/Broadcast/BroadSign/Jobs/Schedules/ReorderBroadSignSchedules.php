<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReorderBroadSignSchedules.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Schedules;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Models\Bundle;

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
     * @param BroadSignConfig $config
     * @param int             $campaignId
     *
     */
    public function __construct(BroadSignConfig $config, int $campaignId) {
        parent::__construct($config);

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
            $bundles = Bundle::bySchedule($this->getAPIClient(), $schedule->id);

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
