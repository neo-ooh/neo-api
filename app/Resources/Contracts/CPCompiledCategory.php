<?php

namespace Neo\Resources\Contracts;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CPCompiledCategory extends Data {
    public function __construct(
        public int            $id,

        public int            $property_id,

        #[DataCollectionOf(CPCompiledProduct::class)]
        public DataCollection $products,

        public float          $impressions,
        public float          $faces_count,
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
