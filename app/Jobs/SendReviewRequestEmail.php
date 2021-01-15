<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - CreateUserLibrary.php
 */

namespace Neo\Jobs;

use Egulias\EmailValidator\Exception\AtextAfterCFWS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\Capability;
use Neo\Mails\ReviewRequestEmail;
use Neo\Models\Actor;
use Neo\Models\Library;
use Neo\Models\Schedule;

/**
 * Class SendReviewRequestEmail
 * Send an email for a schedule review to the appropriate actor
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
    public function __construct (int $scheduleID) {
        $this->scheduleID = $scheduleID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle (): void {
        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($this->scheduleID);

        // If the schedule is not locked, do nothing.
        if(!$schedule->locked) {
            return;
        }

        // We need to determine who is responsible for reviewing this schedule
        $reviewers = $this->getReviewers($schedule);

        /** @var Actor $reviewer */
        foreach($reviewers as $reviewer) {
            Log::debug('reviewer');
            Mail::to($reviewer)->send(new ReviewRequestEmail($schedule));
        }
    }

    public function getReviewers(Schedule $schedule): Collection {
        // We start by the owner of the campaign and we move upward until we found someone
        $actor = $schedule->campaign->owner;

        do {
            if($actor->hasCapability(Capability::contents_review())) {
                if($actor->is_group) {
                    // If the actor is a group, all its direct member who are not groups are returned
                    return $actor->getAccessibleActors(true, true, false, false)
                                         ->filter(fn($actor) => !$actor->is_group);
                }

                return new Collection($actor);
            }

            $actor = $actor->parent;
        } while($actor !== null);

        return new Collection();
    }
}
