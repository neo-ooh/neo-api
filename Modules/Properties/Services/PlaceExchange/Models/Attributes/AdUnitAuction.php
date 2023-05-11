<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitAuction.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;

class AdUnitAuction extends Data {
    public function __construct(
        public int    $at,       // First Price (1)
        public float  $bidfloor, // Price
        public string $bidfloorcur, // CAD, USD
    ) {
    }
}
