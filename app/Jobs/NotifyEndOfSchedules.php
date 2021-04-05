<?php

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Neo\Mails\EndOfScheduleNotificationEmail;
use Neo\Models\Schedule;

/**
 * Class NotifyEndOfSchedules
 *
 * @package Neo\Jobs
 *
 * This job takes all the schedules that are about to end their broadcast, and warn the owner the schedule and the owner of the campaign of it.
 * If the campaign is a group, its direct children are warned. The schedule's owner is always warned.
 */
class NotifyEndOfSchedules implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle() {
        // Start by selecting all the schedules that are about to end
        $schedules = Schedule::query()->where("end_date", ">=", Date::now()->addDay()->startOfDay())
                                      ->where("end_date", "<", Date::now()->addDay()->nextWeekday()->endOfDay())
                                      ->get()
        // Make sure all the selected schedules are actually approved
                                      ->filter(fn($schedule) => $schedule->is_approved)
                                      ->load(["owner:id,name,email", "campaign.owner"]);

        // Now we go schedule by schedule, select the actors that needs to be warned and send the emails
        /** @var Schedule $schedule */
        foreach ($schedules as $schedule) {
            $dest = new Collection($schedule->owner);

            $dest->push($schedule
                ->campaign
                ->owner
                ->direct_children
                ->filter(fn($actor) => !$actor->is_group && !$actor->is_locked));

            $dest->each(fn($recipient) =>
                Mail::to($recipient)->send(new EndOfScheduleNotificationEmail($recipient, $schedule))
            );
        }
    }
}
