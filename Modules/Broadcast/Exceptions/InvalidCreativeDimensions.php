<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidCreativeDimensions.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidCreativeDimensions extends BaseException {
    public function __construct(int $expectedWidth, int $expectedHeight, int $foundWidth, int $foundHeight) {
        parent::__construct("Invalid creative dimensions. Found {$foundWidth}x$foundHeight, expected {$expectedWidth}x$expectedHeight", "creatives.bad-dimensions");
    }
}
