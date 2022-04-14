<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Pricelist.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int                                   $id
 * @property string                                $name
 * @property string                                $description
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 *
 * @property Collection<PricelistProductsCategory> $categories
 */
class Pricelist extends Model {
    protected $table = "pricelists";

    protected $primaryKey = "id";

    protected $fillable = [
        "name",
        "description"
    ];

    public function categories(): BelongsToMany {
        return $this->belongsToMany(ProductCategory::class, "pricelists_products_categories", "pricelist_id", "products_category_id")
                    ->using(PricelistProductsCategory::class)
                    ->as("pricing")
                    ->withPivot([
                        "pricelist_id",
                        "products_category_id",
                        "pricing",
                        "value",
                        "min",
                        "max"
                    ]);
    }
}
