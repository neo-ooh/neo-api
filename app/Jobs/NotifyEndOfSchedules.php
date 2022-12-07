<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NotifyEndOfSchedules.php
 */

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Neo\Enums\ActorType;
use Neo\Mails\EndOfScheduleNotificationEmail;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Schedule;

/**
 * Class NotifyEndOfSchedules
 *
 * @package Neo\Jobs
 *
 * This job takes all the schedules that are about to end their broadcast, and warn the owner the schedule and the owner of the
 * campaign of it. If the campaign is a group, its direct children are warned. The schedule's owner is always warned.
 */
class NotifyEndOfSchedules implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {
    }

    public function handle() {
        // Start by selecting all the schedules that are about to end
        /** @var Collection<Schedule> $schedules */
        $schedules = Schedule::query()->where("end_date", ">=", Date::now()->addDay()->startOfDay())
                             ->where("end_date", "<", Date::now()->addDay()->nextWeekday()->endOfDay())
                             ->get()
            // Make sure all the selected schedules are actually approved
                             ->filter(fn(Schedule $schedule) => $schedule->details->is_approved)
                             ->load(["owner:id,name,email", "campaign.owner"]);

        // Now we go schedule by schedule, select the actors that needs to be warned and send the emails/** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $dest = collect([$schedule->owner,
                             $schedule->campaign->parent,
                             ...$schedule->campaign->parent->direct_children])
                ->unique()
                ->filter(fn(Actor $actor) => $actor->type === ActorType::User && !$actor->is_locked);

            $dest->each(fn($recipient) => Mail::to($recipient->email)
                                              ->send(new EndOfScheduleNotificationEmail($recipient, $schedule))
            );
        }
    }
}
