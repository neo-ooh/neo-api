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
use Neo\Models\Schedule;

class ReviewRequestEmail extends ReliableEmail {
    use Queueable, SerializesModels;

    /**
     * @var Schedule
     */
    public Schedule $schedule;

    /**
     * Create a new message instance.
     *
     * @param Schedule $schedule
     */
    public function __construct(Schedule $schedule) {
        parent::__construct();

        $this->schedule = $schedule;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self {
        return $this->view('emails.review-schedule')
                    ->text('emails.review-schedule-text');
    }
}
