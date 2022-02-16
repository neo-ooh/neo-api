<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReliableEmail.php
 */

namespace Neo\Mails;

use Illuminate\Mail\Mailable;
use Symfony\Component\Mime\Email;

abstract class ReliableEmail extends Mailable {
    public $locale;

    public function __construct() {
        // Set DKIM signature
        $this->withSymfonyMessage(function (Email $message) {
//            $signer      = new DkimSigner(config('mail.dkim.private-key'), config('mail.dkim.domain'), config('mail.dkim.selector'));
//            $signedEmail = $signer->sign($message, (new DkimOptions())->toArray());
//
//            $message->setHeaders($signedEmail->getHeaders());
//            $message->setBody($signedEmail->getBody());
        });
    }
}
