<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - InvalidCreativeDimensions.php
 */

namespace Neo\Exceptions;

class InvalidCreativeDimensions extends BaseException {
    protected $code = "creative.bad-dimensions";
    protected $message = "Creative has invalid dimensions";
}
