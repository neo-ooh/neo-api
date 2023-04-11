<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledProduct.php
 */

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

        public array            $filters,

        public float            $discounted_media_value = 0,

        public string           $production_cost = 'off',
        public float            $production_cost_value = 0,
    ) {
    }
}
