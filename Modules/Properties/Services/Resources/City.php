<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - City.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Spatie\LaravelData\Data;

class City extends Data {
    public function __construct(
        /**
         * The city name, in locale language
         */
        public string $name,

        /**
         * @var string Two letters code of the province of the city
         */
        public string $province_slug,
    ) {
    }
}
