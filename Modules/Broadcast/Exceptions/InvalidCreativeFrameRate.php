<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidCreativeFrameRate.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidCreativeFrameRate extends BaseException {
    public function __construct(string $framerate) {
        parent::__construct("Invalid framerate. Received {$framerate}fps, expected a value between 23.9 and 30fps.", "creatives.bad-framerate");
    }
}
