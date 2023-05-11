<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitPlanning.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;

class AdUnitPlanning extends Data {
    public function __construct(
        public float|null  $base_rate,
        public string|null $rate_cur,
    ) {
    }
}
