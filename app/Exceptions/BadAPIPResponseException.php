<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BadAPIPResponseException.php
 */

namespace Neo\Exceptions;

class BadAPIPResponseException extends \Exception {
    public function __construct(string $message = "", int $code = 0) {
        parent::__construct($message, $code);
    }
}
