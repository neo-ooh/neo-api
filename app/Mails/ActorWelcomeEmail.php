<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ActorWelcomeEmail.php
 */

namespace Neo\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Actor;
use Neo\Models\SignupToken;

class ActorWelcomeEmail extends ReliableEmail {
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $signupToken;

    /**
     * @var Actor
     */
    public $actor;

    public $subject = "Bienvenue chez les services web Neo-OOH — Welcome to Neo-OOH web-services";

    /**
     * Create a new message instance.
     *
     * @param SignupToken $signupToken
     */
    public function __construct(SignupToken $signupToken) {
        parent::__construct();

        $this->signupToken = $signupToken->token;
        $this->actor       = $signupToken->actor;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self {
        return $this->subject("Bienvenue — Welcome")
                    ->view('emails.welcome')
                    ->text('emails.welcome-text');
    }
}

