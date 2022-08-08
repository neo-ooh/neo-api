<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisableBroadSignSchedule.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Schedules;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Services\BroadSign\BroadSignConfig;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Bundle as BSBundle;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Schedule as BSSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;

/**
 * Class DisableBroadSignSchedule
 * Disable a broadsign schedule, effectively stopping its broadcast
 *
 * @package Neo\Jobs
 */
class DisableBroadSignSchedule extends BroadSignJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the schedule in Access
     */
    protected int $broadsignScheduleId;

    public function uniqueId(): int {
        return $this->broadsignScheduleId;
    }


    /**
     * Create a new job instance.
     *
     * @param int $broadsignScheduleId ID of the schedule in Access
     *
     * @return void
     */
    public function __construct(BroadSignConfig $config, int $broadsignScheduleId) {
        parent::__construct($config);

        $this->broadsignScheduleId = $broadsignScheduleId;
    }

    /**
     * Execute the job
     *
     * @return void
     */
    public function handle(): void {
        // Deactivate the schedule
        $bsSchedule = BSSchedule::get($this->getAPIClient(), $this->broadsignScheduleId);

        if ($bsSchedule === null) {
            // We do not throw error on schedule not found here as we are already trying to deactivate it.
            return;
        }

        $bsSchedule->active = false;
        $bsSchedule->weight = 0;
        $bsSchedule->save();

        // Deactivate the schedule's bundles
        $bsBundles = BSBundle::getBySchedule($this->getAPIClient(), $this->broadsignScheduleId);

        foreach ($bsBundles as $bsBundle) {
            $bsBundle->active = false;
            $bsBundle->save();
        }
    }
}
