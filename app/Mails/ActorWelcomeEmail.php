<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Actor;
use Neo\Models\SignupToken;
use Swift_Message;
use Swift_Signers_DKIMSigner;

class ActorWelcomeEmail extends Mailable {
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $signupToken;

    /**
     * @var Actor
     */
    public $actor;

    /**
     * Create a new message instance.
     *
     * @param SignupToken $signupToken
     */
    public function __construct (SignupToken $signupToken) {
        $this->signupToken = $signupToken->token;
        $this->actor = $signupToken->actor;

        // Add DKIM info
//        $this->withSwiftMessage(function(Swift_Message $message) {
//            $signer = new Swift_Signers_DKIMSigner(config('mail.dkim.private-key'), config('mail.dkim.domain'), config('mail.dkim.selector'));
//            $message->attachSigner($signer);
//        });
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build (): self {
        return $this->subject("Bienvenue — Welcome")
            ->view('emails.welcome')
            ->text('emails.welcome-text');
    }
}

