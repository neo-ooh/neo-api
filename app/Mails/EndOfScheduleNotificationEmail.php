<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - EndOfScheduleNotificationEmail.php
 */

namespace Neo\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Schedule;

class EndOfScheduleNotificationEmail extends Mailable {
    use Queueable, SerializesModels;

    public Actor $actor;
    public Schedule $schedule;

    /**
     * Create a new message instance.
     *
     * @param Actor    $actor
     * @param Schedule $schedule
     */
    public function __construct(Actor $actor, Schedule $schedule) {
        $this->actor    = $actor;
        $this->schedule = $schedule;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        App::setLocale($this->actor->locale);

        return $this->subject("Fin de diffusion - End of broadcast")
                    ->view("emails.end-of-schedule")
                    ->text("emails.end-of-schedule-text");
    }
}
