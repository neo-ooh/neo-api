<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Geolocation.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Spatie\LaravelData\Data;

class Geolocation extends Data {
    public function __construct(
        public float $longitude,
        public float $latitude,
    ) {
    }
}
