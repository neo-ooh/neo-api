<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - RecoverPasswordEmail.php
 */

namespace Neo\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Neo\Models\RecoveryToken;
use Neo\Models\TwoFactorToken;

class RecoverPasswordEmail extends Mailable {
    use Queueable, SerializesModels;

    /**
     * @var TwoFactorToken
     */
    public $recoveryToken;

    /**
     * Create a new message instance.
     *
     * @param RecoveryToken $recoveryToken
     */
    public function __construct (RecoveryToken $recoveryToken) {
        $this->recoveryToken = $recoveryToken->token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build (): self {
        return $this->view('emails.auth.password-recovery')
                    ->subject('Connexion aux services web Neo-ooh — Neo-ooh web services connection');
    }
}
