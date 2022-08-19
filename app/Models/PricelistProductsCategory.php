<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - pricelist_products_category.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Neo\Enums\PricingType;

/**
 * @property int         $pricelist_id
 * @property int         $products_category_id
 * @property PricingType $pricing
 * @property double      $value
 * @property double|null $min
 * @property double|null $max
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class PricelistProductsCategory extends Pivot {
    protected $table = "pricelists_products_categories";

    protected $casts = [
        "pricing" => PricingType::class,
    ];

    protected $fillable = [
        "pricelist_id",
        "products_category_id",
        "pricing",
        "value",
        "min",
        "max"
    ];

    public function getRouteKeyName() {
        return "products_category_id";
    }
}
