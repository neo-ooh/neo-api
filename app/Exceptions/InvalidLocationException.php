<?php

namespace Neo\Exceptions;

use Exception;
use Neo\Services\Weather\Location;
use Throwable;

class InvalidLocationException extends BaseException {
    protected $code = "services.weather.invalid-location";

    public function __construct(Location $location, $code = 0, Throwable $previous = null) {
        parent::__construct("Invalid Location: $location->city, $location->province, $location->country", $code, $previous);
    }
}
