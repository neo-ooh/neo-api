<?php

namespace Neo\Mails;

use Illuminate\Mail\Mailable;
use Swift_Message;
use Swift_Signers_DKIMSigner;

abstract class ReliableEmail extends Mailable {
    public $locale;

    public function __construct() {
        // Set DKIM signature
        $this->withSwiftMessage(function(Swift_Message $message) {
            $signer = new Swift_Signers_DKIMSigner(config('mail.dkim.private-key'), config('mail.dkim.domain'), config('mail.dkim.selector'));
            $message->attachSigner($signer);
        });
    }
}
