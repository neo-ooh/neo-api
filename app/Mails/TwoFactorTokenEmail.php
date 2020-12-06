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

class TwoFactorTokenEmail extends Mailable {
    use Queueable, SerializesModels;

    /**
     * @var TwoFactorToken
     */
    public $token;

    /**
     * Create a new message instance.
     *
     * @param TwoFactorToken $token
     */
    public function __construct (TwoFactorToken $token) {
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
