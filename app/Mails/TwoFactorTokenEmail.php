<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - TwoFactorTokenEmail.php
 */

namespace Neo\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Neo\Models\TwoFactorToken;

class TwoFactorTokenEmail extends ReliableEmail {
    use Queueable, SerializesModels;

    /**
     * @var TwoFactorToken
     */
    public $token;

    public $subject = "Connexion aux services web Neo-ooh — Neo-ooh web services connection";

    /**
     * Create a new message instance.
     *
     * @param TwoFactorToken $token
     */
    public function __construct (TwoFactorToken $token) {
        parent::__construct();

        $this->token = $token->token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build (): self {
        return $this->view('emails.auth.two-fa-token');
    }
}
