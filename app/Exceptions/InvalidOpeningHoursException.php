<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidOpeningHoursException.php
 */

namespace Neo\Exceptions;

use Neo\Models\Property;
use Throwable;

class InvalidOpeningHoursException extends BaseException {
    public function __construct(Property $property, int $code = -1, ?Throwable $previous = null) {
        parent::__construct("Invalid opening hours for property #{$property->getKey()} - {$property->actor->name}", "property.invalid-opening-hours", 500, $previous);
    }
}
