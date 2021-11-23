<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Product.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int                         $id
 * @property int                         $property_id
 * @property int                         $category_id
 * @property string                      $name_en
 * @property string                      $name_fr
 * @property int                         $quantity
 * @property int                         $unit_price
 * @property boolean                     $is_bonus
 * @property int                         $external_id
 * @property int                         $external_variant_id
 * @property int                         $external_linked_id
 * @property Carbon                      $created_at
 * @property Carbon                      $updated_at
 *
 * @property \Neo\Models\Property        $property
 * @property Collection<ProductCategory> $products_categories
 */
class Product extends Model {
    protected $table = "products";

    protected $primaryKey = "id";
    public $incrementing = false;

    protected $fillable = [
        "property_id",
        "category_id",
        "name_en",
        "name_fr",
        "quantity",
        "unit_price",
        "is_bonus",
        "external_id",
        "external_variant_id",
        "external_linked_id",
    ];

    protected $casts = [
        "is_bonus" => "boolean",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "property_id");
    }

    public function category(): BelongsTo {
        return $this->belongsTo(ProductCategory::class, "category_id", "id");
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
    public static function scopeWhereInMultiple(Builder $query, array $columns, array $values) {
        collect($values)
            ->transform(function ($v) use ($columns) {
                $clause = [];
                foreach ($columns as $index => $column) {
                    $clause[] = [$column, '=', $v[$index]];
                }
                return $clause;
            })
            ->each(function ($clause, $index) use ($query) {
                $query->where($clause, null, null, $index === 0 ? 'and' : 'or');
            });

        return $query;
    }
}