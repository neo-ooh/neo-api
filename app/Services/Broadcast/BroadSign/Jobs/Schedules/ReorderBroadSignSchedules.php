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
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignConfig;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Bundle;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;

/**
 * Class UpdateBroadSignSchedule
 * Update a BroadSign schedule to reflect the changes made to its counterpart in Access.
 *
 * @package Neo\Jobs
 *
 * @warning This does not update the broadcasting status of the schedule, only its properties.
 * @see     UpdateBroadSignScheduleStatus
 */
class ReorderBroadSignSchedules extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int Id of the campaign whose schedules needs to be updated
     */
    protected int $campaignId;

    public function uniqueId(): int {
        return $this->campaignId;
    }


    /**
     * Create a new job instance.
     *
     * @param \Neo\Modules\Broadcast\Services\BroadSign\BroadSignConfig $config
     * @param int                                                       $campaignId
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
                             ->whereNotNull("external_id_2")->get();

        // For each schedule, we need to retrieve its bundle, and update its position.
        /** @var \Neo\Modules\Broadcast\Models\Schedule $schedule */
        foreach ($schedules as $schedule) {
            $bundles = Bundle::getBySchedule($this->getAPIClient(), $schedule->external_id_2);

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
