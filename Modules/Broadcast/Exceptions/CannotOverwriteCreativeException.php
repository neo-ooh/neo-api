<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotOverwriteCreativeException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class CannotOverwriteCreativeException extends BaseException {
    public function __construct() {
        parent::__construct("A creative is already present", "creatives.already-exist");
    }
}
