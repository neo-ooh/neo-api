<?php

namespace Neo\Services\Broadcast\BroadSign\API;

class BadAPIPResponseException extends \Exception {
    public function __construct(string $message = "", int $code = 0) {
        parent::__construct($message, $code);
    }
}
