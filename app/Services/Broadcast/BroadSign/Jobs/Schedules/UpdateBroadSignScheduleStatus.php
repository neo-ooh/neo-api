<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateBroadSignScheduleStatus.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs\Schedules;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Jobs\BroadSignJob;
use Neo\Services\Broadcast\BroadSign\Jobs\Campaigns\UpdateBroadSignCampaign;
use Neo\Services\Broadcast\BroadSign\Models\Schedule as BSSchedule;

/**
 * Class UpdateBroadSignScheduleStatus
 * Update a BroadSign schedule status to reflect the changes made to its counterpart in Access.
 *
 * @package Neo\Jobs
 *
 */
class UpdateBroadSignScheduleStatus extends BroadSignJob implements ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the updated schedule in Access
     */
    protected int $scheduleID;

    public function uniqueId(): int {
        return $this->scheduleID;
    }


    /**
     * Create a new job instance.
     *
     * @param int $scheduleID ID of the updated schedule in Access
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
     * @throws Exception
     * @throws Exception
     */
    public function handle(): void {
        /** @var Schedule $schedule */
        $schedule = Schedule::query()->find($this->scheduleID);

        if (!$schedule || !$schedule->external_id_2) {
            // This schedule doesn't exist or doesn't have a BroadSign ID, do nothing.
            return;
        }

        // We update the broadsign schedule based on its Access counterpart's status
        $bsSchedule         = BSSchedule::get($this->getAPIClient(), $schedule->external_id_2);
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
            UpdateBroadSignCampaign::dispatch($this->config, $schedule->campaign_id);
        }
    }
}
