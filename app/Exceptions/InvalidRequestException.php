<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidRequestException.php
 */

namespace Neo\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidRequestException extends HttpException {
    public function __construct($message = null, \Throwable $previous = null, array $headers = [], $code = 0) {
        parent::__construct(400, $message, $previous, $headers, $code);
    }
}
