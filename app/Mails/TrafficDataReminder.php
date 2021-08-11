<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficDataReminder.php
 */

namespace Neo\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Neo\Models\Actor;

class TrafficDataReminder extends ReliableEmail
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Actor $actor, public Carbon $date)
    {
        parent::__construct();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        App::setLocale($this->actor->locale);

        return $this->locale($this->actor->locale)
                    ->subject(__("emails.traffic-reminder-subject"))
                    ->replyTo("mallsupport@neo-ooh.com")
                    ->view('emails.traffic-reminder');
    }
}
