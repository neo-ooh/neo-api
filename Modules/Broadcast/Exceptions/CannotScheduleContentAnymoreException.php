<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotScheduleContentAnymoreException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class CannotScheduleContentAnymoreException extends BaseException {
    public function __construct() {
        parent::__construct("Scheduling this content would exceed its scheduling limit.", "contents.schedule-limit-reached");
    }
}
