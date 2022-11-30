<?php

namespace Neo\Resources\Contracts;

use Spatie\LaravelData\Data;

class FlightPerformanceDatum extends Data {
    public function __construct(
        public int      $flight_id,
        public int|null $network_id,
        public string   $recorded_at,
        public int      $repetitions,
        public int      $impressions,
    ) {
    }
}
