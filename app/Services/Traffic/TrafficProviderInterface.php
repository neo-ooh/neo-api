<?php

namespace Neo\Services\Traffic;

use Carbon\Traits\Date;
use Neo\Models\Property;

interface TrafficProviderInterface {
    public function getTraffic(Property $property, Date $from, Date $to): int;
}
