<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitSlot.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;

class AdUnitSlot extends Data {
    public function __construct(
        public int   $h,            // height in pixels
        public float $max_duration, // seconds
        public float $min_duration, // seconds
        public int   $w, // width in pixels
    ) {
    }
}
