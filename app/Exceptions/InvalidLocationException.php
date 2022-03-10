<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidLocationException.php
 */

namespace Neo\Exceptions;

use Throwable;

class InvalidLocationException extends BaseException {
    protected $code = "services.weather.invalid-location";

    public function __construct(string $country, string $province, string $city, $code = 0, Throwable $previous = null) {
        parent::__construct("Invalid Location: $country, $province, $city", 422, $previous);
    }
}
