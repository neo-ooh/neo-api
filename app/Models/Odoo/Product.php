<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Product.php
 */

namespace Neo\Models\Odoo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * @property int                         $property_id
 * @property int                         $product_category_id
 * @property int                         $odoo_id
 * @property string                      $name
 * @property int                         $quantity
 * @property int                         $odoo_variant_id
 * @property Carbon                      $created_at
 * @property Carbon                      $updated_at
 *
 * @property \Neo\Models\Property        $property
 * @property Collection<ProductCategory> $products_categories
 */
class Product extends Model {
    use HasCompositePrimaryKey;

    protected $table = "odoo_properties_products";

    protected $primaryKey = ["property_id", "product_category_id"];

    protected $fillable = [
        "property_id",
        "product_category_id",
        "odoo_id",
        "name",
        "quantity",
        "unit_price",
        "odoo_variant_id",
        "is_bonus",
    ];

    public $incrementing = false;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "property_id");
    }

    public function category(): BelongsTo {
        return $this->belongsTo(ProductCategory::class, "product_category_id", "id");
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $query
     * @param array   $columns
     * @param array   $values
     *
     * @return Builder
     */
    public static function scopeWhereInMultiple(Builder $query, array $columns, array $values)
    {
        collect($values)
            ->transform(function ($v) use ($columns) {
                $clause = [];
                foreach ($columns as $index => $column) {
                    $clause[] = [$column, '=', $v[$index]];
                }
                return $clause;
            })
            ->each(function($clause, $index) use ($query) {
                $query->where($clause, null, null, $index === 0 ? 'and' : 'or');
            });

        return $query;
    }
}
