<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistProductsCategory.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Neo\Modules\Properties\Enums\PriceType;

/**
 * @property int         $pricelist_id
 * @property int         $products_category_id
 * @property PriceType   $pricing
 * @property double      $value
 * @property double|null $min
 * @property double|null $max
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class PricelistProductsCategory extends Pivot {
    protected $table = "pricelists_products_categories";

    protected $casts = [
        "pricing" => PriceType::class,
    ];

    protected $fillable = [
        "pricelist_id",
        "products_category_id",
        "pricing",
        "value",
        "min",
        "max",
    ];

    protected $touches = [
        "product_category",
    ];

    public function getRouteKeyName() {
        return "products_category_id";
    }

    public function product_category() {
        return $this->hasOne(ProductCategory::class, "id", "products_category_id");
    }
}
