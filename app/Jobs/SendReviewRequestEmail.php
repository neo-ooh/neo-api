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

        $reviewers->each(fn($reviewer) =>
            Mail::to($reviewer)->send(new ReviewRequestEmail($reviewer, $schedule))
        );
    }

    public function getReviewers(Schedule $schedule): Collection {
        // We start by the owner of the campaign and we move upward until we found someone
        $actor = $schedule->campaign->owner;

        do {
            // Is this actor a group ?
            if($actor->is_group) {
                // Does this group has actor with the proper capability ?
                $reviewers = $actor->getAccessibleActors(true, true, false, false)
                                   ->filter(fn($child) => !$child->is_group && $child->hasCapability(Capability::contents_review()))
                                   ->each(fn($actor) => $actor->unsetRelations());

                if($reviewers->count() > 0) {
                    return $reviewers;
                }
            }

            if($actor->hasCapability(Capability::contents_review())) {
                // This actor has the proper capability, use it
                return (new Collection())->push($actor);
            }

            // No match, go up
            $actor = $actor->parent;
        } while($actor !== null);

        return new Collection();
    }
}
