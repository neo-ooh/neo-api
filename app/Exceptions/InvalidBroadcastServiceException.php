<?php

namespace Neo\Exceptions;

use Exception;
use Throwable;

class InvalidBroadcastServiceException extends Exception
{
    public function __construct($type = "", $code = -1, Throwable $previous = null) {
        parent::__construct("Invalid Broadcast Service $type", $code, $previous);
    }
}
