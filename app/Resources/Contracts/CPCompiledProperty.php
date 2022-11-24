<?php

namespace Neo\Resources\Contracts;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CPCompiledProperty extends Data {
    public function __construct(
        public int            $id,

        #[DataCollectionOf(CPCompiledCategory::class)]
        public DataCollection $categories,

        public int            $traffic,
        public float          $faces_count,
        public float          $impressions,
        public float          $media_value,

        public float          $price,
        public float          $cpm,
        public float          $cpmPrice,

        public bool           $isDiscounted,
        public float          $discount_amount,
        public bool           $hasDiscountError,
    ) {
    }
}
