<?php

namespace Neo\Services\Traffic;

use Carbon\Traits\Date;
use InvalidArgumentException;
use Neo\Models\Property;
use Neo\Models\TrafficSource;

abstract class Traffic {
    public const LINKETT = "linkett";

    public static function from(TrafficSource $source) {
        switch ($source->type) {
            case static::LINKETT:
                return new LinkettAPIAdapter($source->settings);
        }

        throw new InvalidArgumentException("Traffic source type $source->type is not supported");
    }
}
