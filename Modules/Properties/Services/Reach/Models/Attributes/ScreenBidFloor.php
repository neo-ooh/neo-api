<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenBidFloor.php
 */

namespace Neo\Modules\Properties\Services\Reach\Models\Attributes;

use Spatie\LaravelData\Data;

class ScreenBidFloor extends Data {
    public function __construct(
        public float          $floor,
        public ScreenCurrency $currency,
    ) {
    }
}
