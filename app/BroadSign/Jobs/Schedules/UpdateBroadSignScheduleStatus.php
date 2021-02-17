<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateBroadSignScheduleStatus.php
 */

namespace Neo\BroadSign\Jobs\Schedules;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Jobs\BroadSignJob;
use Neo\BroadSign\Jobs\Campaigns\UpdateBroadSignCampaign;
use Neo\BroadSign\Models\Schedule as BSSchedule;
use Neo\Models\Schedule;

/**
 * Class UpdateBroadSignScheduleStatus
 * Update a BroadSign schedule status to reflect the changes made to its counterpart in Access.
 *
 * @package Neo\Jobs
 *
 */
class UpdateBroadSignScheduleStatus extends BroadSignJob {
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
    public function __construct(int $scheduleID) {
        $this->scheduleID = $scheduleID;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     * @throws Exception
     */
    public function handle(): void {
        /** @var Schedule $schedule */
        $schedule = Schedule::query()->find($this->scheduleID);

        if (!$schedule->broadsign_schedule_id) {
            // This schedule doesn't have a BroadSign ID, do nothing.
            return;
        }

        // We update the broadsign schedule based on its Access counterpart's status
        $bsSchedule         = BSSchedule::get($schedule->broadsign_schedule_id);
        $bsSchedule->active = $schedule->is_approved;
        $bsSchedule->save();

        $bsCampaign = $bsSchedule->campaign();

        // If the schedule is active, but the campaign is not, we need to promote the campaign
        if ($bsSchedule->active && $bsCampaign->state === 0) {
            $bsCampaign->state = 1;
            $bsCampaign->save();
        }

        // Finally, trigger an update of the campaign if its saturation is automatic
        if ($schedule->campaign->loop_saturation === 0) {
            UpdateBroadSignCampaign::dispatch($schedule->campaign_id);
        }
    }
}
