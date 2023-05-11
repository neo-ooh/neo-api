<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitRestrictions.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;

class AdUnitRestrictions extends Data {
    public function __construct(
        public array $badomain = [],
        public array $battr = [],
        public array $bbundle = [],
        public array $bbuyer = [],
        public array $bcat = [],
        public array $bcid = [],
        public array $bcrid = [],
        public array $bseat = [],
        public array $btype = [],
        public array $wlang = [],
    ) {
    }
}
