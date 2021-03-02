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
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Neo\Models\Actor;
use Neo\Models\RecoveryToken;
use Neo\Models\TwoFactorToken;

class RecoverPasswordEmail extends ReliableEmail {
    use Queueable, SerializesModels;

    public $recoveryToken;
    public $actor;

    public $subject = "Connexion aux services web Neo-ooh — Neo-ooh web services connection";

    /**
     * Create a new message instance.
     *
     * @param RecoveryToken $recoveryToken
     */
    public function __construct (Actor $actor, RecoveryToken $recoveryToken) {
        parent::__construct();
        $this->actor = $actor;
        $this->recoveryToken = $recoveryToken->token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build (): self {
        App::setLocale($this->actor->locale);

        return $this->view('emails.auth.password-recovery');
    }
}
