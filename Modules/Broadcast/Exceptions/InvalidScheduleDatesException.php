<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidScheduleDatesException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidScheduleDatesException extends BaseException {
    public function __construct() {
        parent::__construct("Invalid schedule broadcast dates, make sure they fit in the schedule's campaign.", "schedule.invalid-dates");
    }
}
