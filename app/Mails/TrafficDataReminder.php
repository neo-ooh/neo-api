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
use Illuminate\Support\Facades\App;
use Neo\Models\Actor;

class TrafficDataReminder extends ReliableEmail
{
    use Queueable, SerializesModels;

    public Actor $actor;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Actor $actor)
    {
        parent::__construct();
        $this->actor = $actor;
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
