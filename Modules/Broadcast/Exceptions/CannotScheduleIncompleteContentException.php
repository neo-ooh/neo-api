<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotScheduleIncompleteContentException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class CannotScheduleIncompleteContentException extends BaseException {
    public function __construct() {
        parent::__construct("Cannot schedule an incomplete content.", "contents.incomplete");
    }
}
