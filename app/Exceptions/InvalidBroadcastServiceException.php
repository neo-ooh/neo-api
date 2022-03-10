<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidBroadcastServiceException.php
 */

namespace Neo\Exceptions;

use Exception;
use Throwable;

class InvalidBroadcastServiceException extends Exception
{
    public function __construct($type = "", $code = -1, Throwable $previous = null) {
        parent::__construct("Invalid Broadcast Service $type", $code, $previous);
    }
}
