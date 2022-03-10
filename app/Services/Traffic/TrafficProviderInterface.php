<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficProviderInterface.php
 */

namespace Neo\Services\Traffic;

use Carbon\Carbon;
use Neo\Models\Property;

interface TrafficProviderInterface {
    public function getTraffic(Property $property, Carbon $from, Carbon $to): int;
}
