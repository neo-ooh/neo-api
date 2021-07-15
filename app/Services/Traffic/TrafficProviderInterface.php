<?php

namespace Neo\Services\Traffic;

use Carbon\Carbon;
use Neo\Models\Property;

interface TrafficProviderInterface {
    public function getTraffic(Property $property, Carbon $from, Carbon $to): int;
}
