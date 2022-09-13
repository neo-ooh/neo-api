<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidCreativeDuration.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidCreativeDuration extends BaseException {
    public function __construct(float $expectedLength, float $foundLength) {
        parent::__construct("Invalid creative duration. Found {$foundLength}s, expected {$expectedLength}s.", "creatives.bad-duration");
    }
}
