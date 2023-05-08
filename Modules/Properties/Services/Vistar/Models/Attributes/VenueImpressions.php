<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - VenueImpressions.php
 */

namespace Neo\Modules\Properties\Services\Vistar\Models\Attributes;

use Spatie\LaravelData\Data;

class VenueImpressions extends Data {
    public function __construct(
        public float $per_spot,
        public float $per_second = 0,
    ) {
    }
}
