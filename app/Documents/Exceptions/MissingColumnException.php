<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MissingColumnException.php
 */

namespace Neo\Documents\Exceptions;

use Neo\Exceptions\BaseException;
use Throwable;

class MissingColumnException extends BaseException {
    public function __construct($column = "", $code = -1, Throwable $previous = null) {
        parent::__construct("Missing column $column in input data", $code, 422, $previous);
    }
}
