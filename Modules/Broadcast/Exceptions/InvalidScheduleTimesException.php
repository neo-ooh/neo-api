<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidScheduleTimesException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Exception;

class InvalidScheduleTimesException extends Exception {
    public function __construct() {
        parent::__construct("Invalid schedule broadcast times, make sure they fit in the schedule's campaign.", "schedules.invalid-times");
    }
}
