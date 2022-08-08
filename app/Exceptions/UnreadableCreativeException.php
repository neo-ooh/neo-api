<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnreadableCreativeException.php
 */

namespace Neo\Exceptions;

class UnreadableCreativeException extends BaseException {
    public function __construct() {
        parent::__construct("File could not be processed. Format may be invalid", "creative.unreadable-file");
    }
}
