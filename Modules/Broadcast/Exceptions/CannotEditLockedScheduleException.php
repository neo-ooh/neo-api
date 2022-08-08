<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotEditLockedScheduleException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class CannotEditLockedScheduleException extends BaseException {
    public function __construct() {
        parent::__construct("You are not allowed to edit a locked schedule.", "schedule.cannot-edit-locked");
    }
}
