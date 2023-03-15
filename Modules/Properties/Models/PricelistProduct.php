<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistProduct.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Neo\Modules\Properties\Enums\PriceType;

/**
 * @property int         $pricelist_id
 * @property int         $product_id
 * @property PriceType   $pricing
 * @property double      $value
 * @property double|null $min
 * @property double|null $max
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class PricelistProduct extends Pivot {
    protected $table = "pricelists_products";

    protected $casts = [
        "pricing" => PriceType::class,
    ];

    protected $fillable = [
        "pricelist_id",
        "product_id",
        "pricing",
        "value",
        "min",
        "max",
    ];

    public function getRouteKeyName() {
        return "product_id";
    }
}
