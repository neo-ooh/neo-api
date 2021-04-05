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
use Neo\Models\Schedule;

class EndOfScheduleNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected Actor $actor;
    protected Schedule $schedule;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Actor $actor,Schedule $schedule)
    {
        $this->actor = $actor;
        $this->schedule = $schedule;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        App::setLocale($this->actor->locale);

        return $this->subject("Fin de diffusion - End of broadcast")
                    ->view("emails.end-of-schedule")
                    ->text("emails.end-of-schedule-text");
    }
}
