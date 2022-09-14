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
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int                                   $id
 * @property string                                $name
 * @property string                                $description
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 *
 * @property Collection<ProductCategory>           $categories
 * @property Collection<Product>                   $products
 *
 * @property Collection<PricelistProductsCategory> $categories_pricings
 * @property Collection<PricelistProduct>          $products_pricings
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

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, "pricelists_products", "pricelist_id", "product_id")
                    ->using(PricelistProduct::class)
                    ->as("pricing")
                    ->withPivot([
                        "pricelist_id",
                        "product_id",
                        "pricing",
                        "value",
                        "min",
                        "max"
                    ]);
    }

    public function categories_pricings(): HasMany {
        return $this->hasMany(PricelistProductsCategory::class, "pricelist_id", "id");
    }

    public function products_pricings(): HasMany {
        return $this->hasMany(PricelistProduct::class, "pricelist_id", "id");
    }

    public function properties(): HasMany {
        return $this->hasMany(Property::class, "pricelist_id", "id");
    }
}
