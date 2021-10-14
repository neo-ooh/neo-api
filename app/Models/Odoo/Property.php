<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Property.php
 */

namespace Neo\Models\Odoo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int                  $property_id
 * @property int                  $odoo_id
 * @property string               $internal_name
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 *
 * @property \Neo\Models\Property $property
 * @property Collection<Product>  $products
 * @property Collection<ProductCategory>  $products_categories
 */
class Property extends Model {
    protected $table = "odoo_properties";

    protected $primaryKey = "property_id";

    public $incrementing = false;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(\Neo\Models\Property::class, "property_id", "actor_id");
    }

    public function products(): HasMany {
        return $this->hasMany(Product::class, "property_id", "property_id");
    }

    public function products_categories(): BelongsToMany {
        return $this->belongsToMany(ProductCategory::class, "odoo_properties_products", "property_id", "product_category_id")
                    ->withPivot(["property_id"])
                    ->distinct();
    }

    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public function computeCategoriesValues() {
        // For each product category, we summed the prices and faces of products in it
        /** @var ProductCategory $products_category */
        foreach($this->products_categories as $products_category) {
            $products = $this->products->where("product_category_id", "=", $products_category->id);

            // As of 2021-10-07, mall posters (OdooId #34) handling is not entirely defined. An exception is therefore setup to
            // limit selection to only one poster at a time. Same behaviour applies to specialty media product as well
            if($products_category->odoo_id === 34 || $products_category->product_type_id === 1) {
                $products_category->quantity = 1;
                $products_category->unit_price = $products->first()->unit_price;
                continue;
            }

            $products_category->quantity = $products->sum("quantity");
            $products_category->unit_price = $products->map(fn($p) => $p->quantity * $p->unit_price)->sum();
        }
    }
}
