<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DayOperatingHours.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Spatie\LaravelData\Data;

class DayOperatingHours extends Data {
    public function __construct(
        /**
         * @var int 1-index day of the week
         */
        public int    $day,

        /**
         * @var bool Tell if the property is closed on this day. If true, `start_at` and `end_at` properties are irrelevant
         */
        public bool   $is_closed,

        /**
         * @var string Start operating period time in 24h format - HH:mm:ss
         */
        public string $start_at,

        /**
         * @var string End operating period time in 24h format - HH:mm:ss
         */
        public string $end_at,

        /**
         * @var int How many minutes this place if open for a day
         */
        public int    $open_length_min,

    ) {

    }
}
