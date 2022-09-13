<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidScheduleBroadcastDaysException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidScheduleBroadcastDaysException extends BaseException {
    public function __construct() {
        parent::__construct("Invalid schedule broadcast days, make sure they fit in the schedule's campaign.", "schedules.invalid-broadcast-days");
    }
}
