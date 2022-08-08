<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidBroadcasterAdapterException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidBroadcasterAdapterException extends BaseException {
    public function __construct($type = "") {
        parent::__construct("Invalid broadcast service '$type'");
    }
}
