<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitMeasurement.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;

class AdUnitMeasurement extends Data {
    public function __construct(
        public float  $duration,            // Typical play duration, used to calculate Per-Play Impression Estimate
        public float  $imp_four_week,       // Per-Play Impression Estimate. This is used to calculate spend and will affect ad serving if no value is registered on the adunit and none sent in the request.
        public float  $imp_x,               // Per-Play Impression Estimate. This is used to calculate spend and will affect ad serving if no value is registered on the adunit and none sent in the request.
        public string $provider, // First Price
    ) {
    }
}
