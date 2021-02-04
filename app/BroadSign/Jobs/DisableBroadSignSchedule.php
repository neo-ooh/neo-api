<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - DisableBroadSignSchedule.php
 */

namespace Neo\BroadSign\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Bundle as BSBundle;
use Neo\BroadSign\Models\Schedule as BSSchedule;
use Neo\Models\Schedule;
use Symfony\Component\Translation\Exception\InvalidResourceException;

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
    protected int $broadsignScheduleId;


    /**
     * Create a new job instance.
     *
     * @param int $broadsignScheduleId ID of the schedule in Access
     *
     * @return void
     */
    public function __construct (int $broadsignScheduleId) {
        $this->broadsignScheduleId = $broadsignScheduleId;
    }

    /**
     * Execute the job
     *
     * @return void
     */
    public function handle (): void {
        if(config("app.env") !== "production") {
            return;
        }

        // Deactivate the schedule
        $bsSchedule = BSSchedule::get($this->broadsignScheduleId);

        if($bsSchedule === null) {
            // We do not throw error on schedule not found here as we are already trying to deactivate it.
            return;
        }

        $bsSchedule->active = false;
        $bsSchedule->weight = 0;
        $bsSchedule->save();

        // Deactivate the schedule's bundle
        $bsBundle = BSBundle::bySchedule($this->broadsignScheduleId);

        if($bsBundle === null) {
            // We do not throw error on bundle not found here as we are already trying to deactivate it.
            throw new InvalidResourceException("BroadSign Bundle for Schedule $this->broadsignScheduleId could not be loaded.");
        }

        $bsBundle->active = false;
        $bsBundle->save();
    }
}
