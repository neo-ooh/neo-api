<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendReviewRequestEmail.php
 */

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Mails\ReviewRequestEmail;
use Neo\Modules\Broadcast\Models\Schedule;

/**
 * Class SendReviewRequestEmail
 * Send an email for a schedule review to the appropriate actor
 *
 * @package Neo\Jobs
 *
 */
class SendReviewRequestEmail implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int ID of the schedule
     */
    protected int $scheduleID;


    /**
     * Create a new job instance.
     *
     * @param int $scheduleID ID of the schedule
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
     */
    public function handle(): void {
        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($this->scheduleID);

        // If the schedule is not locked, do nothing.
        if (!$schedule->is_locked) {
            return;
        }

        // We need to determine who is responsible for reviewing this schedule
        $reviewers = $schedule->campaign->parent->getActorsInHierarchyWithCapability(Capability::contents_review);

        $reviewers->each(fn($reviewer) => Mail::to($reviewer)->send(new ReviewRequestEmail($reviewer, $schedule))
        );
    }
}
