<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - VenueOperatingMinutes.php
 */

namespace Neo\Modules\Properties\Services\Vistar\Models\Attributes;

use Carbon\Carbon;
use Neo\Modules\Properties\Services\Resources\DayOperatingHours;
use Spatie\LaravelData\Data;

class VenueOperatingMinutes extends Data {
    public function __construct(
        public float $local_start_minute_of_week,
        public float $local_end_minute_of_week,
    ) {
    }

    /**
     * @param array $days
     * @return static[]
     */
    public static function buildFromOperatingHours(array $days): array {
        $daysMinutes = [];

        /** @var DayOperatingHours $day */
        foreach ($days as $day) {
            if ($day->is_closed) {
                continue;
            }

            $start        = Carbon::createFromTimeString($day->start_at);
            $startMinutes = (($day->day - 1) * 24 * 60) + ($start->hour * 60) + $start->minute;

            $end        = Carbon::createFromTimeString($day->end_at);
            $endMinutes = (($day->day - 1) * 24 * 60) + ($end->hour * 60) + $end->minute;

            $daysMinutes[] = new static($startMinutes, $endMinutes);
        }

        return $daysMinutes;
    }
}
