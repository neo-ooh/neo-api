<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidFileFormatException.php
 */

namespace Neo\Modules\Demographics\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidFileFormatException extends BaseException {
    public function __construct(string $message) {
        parent::__construct($message, "file.invalid-format");
    }
}
