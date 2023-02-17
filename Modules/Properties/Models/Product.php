<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Product.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\LoopConfiguration;
use Neo\Modules\Properties\Models\Interfaces\WithAttachments;
use Neo\Modules\Properties\Models\Interfaces\WithImpressionsModels;
use Neo\Modules\Properties\Models\Traits\HasImpressionsModels;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int                           $id
 * @property int                           $property_id
 * @property int                           $category_id
 * @property string                        $name_en
 * @property string                        $name_fr
 * @property int|null                      $format_id
 * @property int                           $quantity
 * @property boolean                       $is_sellable
 * @property int                           $unit_price
 * @property boolean                       $is_bonus
 * @property int                           $external_id
 * @property int                           $external_variant_id
 * @property int|null                      $linked_product_id
 * @property int                           $spot_length
 * @property int                           $spots_count
 * @property int                           $extra_spots
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 * @property Carbon                        $deleted_at
 *
 * @property Property                      $property
 * @property ProductCategory               $category
 * @property Format                        $format
 * @property Collection<ImpressionsModel>  $impressions_models
 * @property Collection<LoopConfiguration> $loop_configurations
 * @property Collection<Unavailability>    $unavailabilities
 *
 * @property int                           $locations_count           // Laravel `withCount` result accessor
 * @property Collection<Location>          $locations
 */
class Product extends Model implements WithImpressionsModels, WithAttachments {
    use SoftDeletes;
    use HasImpressionsModels;
    use HasRelationships;
    use HasPublicRelations;

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
        "linked_product_id",
        "spot_length",
        "spots_count",
        "extra_spots",

    ];

    protected $casts = [
        "is_sellable" => "boolean",
        "is_bonus"    => "boolean",
    ];

    public string $impressions_models_pivot_table = "products_impressions_models";

    protected function getPublicRelations() {
        return [
            "property"            => "property",
            "category"            => "category",
            "locations"           => "locations",
            "attachments"         => "attachments",
            "impressions_models"  => ["impressions_models", "category.impressions_models"],
            "loop_configurations" => ["loop_configurations", "category.loop_configurations"],
            "format"              => "format",
            "pricelist"           => "load:pricelist:id",
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }

    public function format(): BelongsTo {
        return $this->belongsTo(Format::class, "format_id", "id");
    }

    public function category(): BelongsTo {
        return $this->belongsTo(ProductCategory::class, "category_id", "id");
    }

    public function campaigns(): BelongsToMany {
        return $this->belongsToMany(Campaign::class, "campaign_locations", "product_id", "campaign_id")
                    ->withPivot(["location_id"])
                    ->withTimestamps();
    }

    public function locations(): BelongsToMany {
        return $this->belongsToMany(Location::class, "products_locations", "product_id", "location_id")
                    ->withPivot(["format_id", "product_id"]);
    }

    public function attachments(): BelongsToMany {
        return $this->belongsToMany(Attachment::class, "products_attachments", "product_id", "attachment_id");
    }

    public function loop_configurations(): HasManyDeep {
        return $this->hasManyDeepFromRelations([$this->format(), (new Format())->loop_configurations()]);
    }

    public function pricelist(): HasOneDeep {
        return $this->hasOneDeepFromRelations([$this->property(), (new Property())->pricelist()]);
    }

    public function unavailabilities(): BelongsToMany {
        return $this->belongsToMany(Unavailability::class, "products_unavailabilities", "property_id", "unavailability_id");
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

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

    public function getLoopConfiguration(Carbon $date): LoopConfiguration|null {
        $configurationValidator = function (LoopConfiguration $loopConfiguration) use ($date) {
            return $loopConfiguration->dateIsInPeriod($date);
        };

        // If the product as a format specified use this one and ignore the category's one.
        if ($this->format_id !== null) {
            return $this->loop_configurations->first($configurationValidator);
        }

        // Default to the category's loop configurations
        return $this->category->loop_configurations->first($configurationValidator);
    }
}
