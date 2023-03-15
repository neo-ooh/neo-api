<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Address.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Spatie\LaravelData\Data;

class Address extends Data {
    public function __construct(
        public string $line_1,
        public string $line_2,

        public City   $city,

        public string $zipcode,
    ) {
    }
}
