<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ReviewRequestEmail.php
 */

namespace Neo\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Neo\Models\Actor;
use Neo\Models\Schedule;

class ReviewRequestEmail extends ReliableEmail {
    use Queueable, SerializesModels;

    /**
     * @var Schedule
     */
    public Schedule $schedule;

    public Actor $actor;

    public $subject = "Programmation en attente de validation — Schedule awaiting approval";

    /**
     * Create a new message instance.
     *
     * @param Schedule $schedule
     */
    public function __construct(Actor $actor, Schedule $schedule) {
        parent::__construct();

        $this->actor = $actor;
        $this->schedule = $schedule;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self {
        App::setLocale($this->actor->locale);

        return $this->view('emails.review-schedule')
                    ->text('emails.review-schedule-text');
    }
}
