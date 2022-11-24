<?php

namespace Neo\Resources\Contracts;

use Spatie\LaravelData\Data;

class CPCompiledProduct extends Data {
    public function __construct(
        public int              $id,

        public int              $property_id,
        public int              $category_id,

        public CPProductPricing $pricing,
        public float            $price_value,

        public float            $unit_price,
        public float            $quantity,
        public float            $traffic,
        public float            $impressions,
        public float            $media_value,
        public float            $spots,

        public bool             $isDiscounted,
        public bool             $hasDiscountError,
        public array|null       $discount,
        public float            $discount_amount,

        public float            $price,
        public float            $cpm,


        public bool             $ignore,
        public bool             $force,
        public bool             $filteredOut,

        public array            $filters
    ) {
    }
}
