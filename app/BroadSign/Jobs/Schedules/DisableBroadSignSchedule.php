<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisableBroadSignSchedule.php
 */

namespace Neo\BroadSign\Jobs\Schedules;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Jobs\BroadSignJob;
use Neo\BroadSign\Models\Bundle as BSBundle;
use Neo\BroadSign\Models\Schedule as BSSchedule;
use Symfony\Component\Translation\Exception\InvalidResourceException;

/**
 * Class DisableBroadSignSchedule
 * Disable a broadsign schedule, effectively stopping its broadcast
 *
 * @package Neo\Jobs
 */
class DisableBroadSignSchedule extends BroadSignJob {
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
    public function __construct(int $broadsignScheduleId) {
        $this->broadsignScheduleId = $broadsignScheduleId;
    }

    /**
     * Execute the job
     *
     * @return void
     */
    public function handle(): void {
        // Deactivate the schedule
        $bsSchedule = BSSchedule::get($this->broadsignScheduleId);

        if ($bsSchedule === null) {
            // We do not throw error on schedule not found here as we are already trying to deactivate it.
            return;
        }

        $bsSchedule->active = false;
        $bsSchedule->weight = 0;
        $bsSchedule->save();

        // Deactivate the schedule's bundles
        $bsBundles = BSBundle::bySchedule($this->broadsignScheduleId);

        if ($bsBundles->count() === 0) {
            // We do not throw error on bundle not found here as we are already trying to deactivate it.
            throw new InvalidResourceException("BroadSign Bundle for Schedule $this->broadsignScheduleId could not be loaded.");
        }

        foreach ($bsBundles as $bsBundle) {
            $bsBundle->active = false;
            $bsBundle->save();
        }
    }
}
