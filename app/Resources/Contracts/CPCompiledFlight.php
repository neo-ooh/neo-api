<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledFlight.php
 */

namespace Neo\Resources\Contracts;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CPCompiledFlight extends Data {
    public function __construct(
        public string         $id,
        public string|null    $name,
        public FlightType     $type,

        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon         $start_date,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon         $end_date,
        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i:s')]
        public Carbon         $start_time,
        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i:s')]
        public Carbon         $end_time,

        public int            $weekdays,

        public int            $length,

        #[DataCollectionOf(CPCompiledProperty::class)]
        public DataCollection $properties,


        public int            $traffic,
        public int            $faces_count,
        public float          $impressions,
        public float          $media_value,

        public array          $discounts,

        public float          $price,
        public float          $cpm,
        public float          $cpmPrice,
    ) {
    }
}
