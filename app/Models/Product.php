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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Interfaces\WithAttachments;
use Neo\Models\Interfaces\WithImpressionsModels;
use Neo\Models\Traits\HasImpressionsModels;

/**
 * @property int                          $id
 * @property int                          $property_id
 * @property int                          $category_id
 * @property string                       $name_en
 * @property string                       $name_fr
 * @property int                          $quantity
 * @property int                          $unit_price
 * @property boolean                      $is_bonus
 * @property int                          $external_id
 * @property int                          $external_variant_id
 * @property int                          $external_linked_id
 * @property int                          $spot_length
 * @property int                          $spots_count
 * @property int                          $extra_spots
 * @property Carbon                       $created_at
 * @property Carbon                       $updated_at
 * @property Carbon                       $deleted_at
 *
 * @property Property                     $property
 * @property ProductCategory              $category
 * @property Collection<ImpressionsModel> $impressions_models
 * @property Collection<Location>         $locations
 */
class Product extends Model implements WithImpressionsModels, WithAttachments {
    use HasImpressionsModels;
    use SoftDeletes;

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
        "spot_length",
        "spots_count",
        "extra_spots",

    ];

    protected $casts = [
        "is_bonus" => "boolean",
    ];

    public string $impressions_models_pivot_table = "products_impressions_models";


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }

    public function category(): BelongsTo {
        return $this->belongsTo(ProductCategory::class, "category_id", "id");
    }

    public function locations(): BelongsToMany {
        return $this->belongsToMany(Location::class, "products_locations", "product_id", "location_id");
    }

    public function attachments(): BelongsToMany {
        return $this->belongsToMany(Attachment::class, "products_attachments", "product_id", "attachment_id");
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

    public function getImpressionModel(Carbon $date): ImpressionsModel|null {
        /**
         * @param ImpressionsModel $model
         * @return bool
         */
        $validateModel = fn(ImpressionsModel $model) => $model->start_month <= $date->month && $date->month <= $model->end_month;

        $model = $this->impressions_models->first($validateModel);

        if (!$model) {
            $model = $this->category->impressions_models->first($validateModel);
        }

        return $model;
    }
}
