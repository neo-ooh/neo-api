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
use Neo\Casts\EnumSetCast;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\LoopConfiguration;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\Interfaces\WithAttachments;
use Neo\Modules\Properties\Models\Interfaces\WithImpressionsModels;
use Neo\Modules\Properties\Models\Misc\ProductPricing;
use Neo\Modules\Properties\Models\Traits\HasImpressionsModels;
use Neo\Modules\Properties\Models\Traits\InventoryResourceModel;
use Neo\Modules\Properties\Services\Resources\DayOperatingHours;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\Geolocation;
use Neo\Modules\Properties\Services\Resources\LocalizedString;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @property int                           $id
 * @property int                           $inventory_resource_id
 * @property int                           $property_id
 * @property int|null                      $category_id
 * @property string                        $name_en
 * @property string                        $name_fr
 * @property int|null                      $format_id
 * @property int                           $quantity
 * @property boolean                       $is_sellable
 * @property int                           $unit_price
 * @property boolean                       $is_bonus
 * @property int|null                      $linked_product_id
 * @property MediaType[]                   $allowed_media_types
 * @property boolean|null                  $allows_audio
 * @property double|null                   $production_cost
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
 * @property Pricelist|null                $pricelist
 * @property ProductPricing|null           $pricing
 *
 * @property Collection<number>            $enabled_inventories
 *
 * @property int                           $locations_count           // Laravel `withCount` result accessor
 * @property Collection<Location>          $locations
 */
class Product extends Model implements WithImpressionsModels, WithAttachments {
    use SoftDeletes;
    use HasImpressionsModels;
    use HasRelationships;
    use HasPublicRelations;
    use InventoryResourceModel;

    protected $table = "products";

    protected $primaryKey = "id";
    public $incrementing = true;

    public InventoryResourceType $inventoryResourceType = InventoryResourceType::Product;

    protected $fillable = [
        "property_id",
        "category_id",
        "name_en",
        "name_fr",
        "quantity",
        "unit_price",
        "is_bonus",
        "linked_product_id",
        "production_cost",
    ];

    protected $casts = [
        "is_sellable"         => "boolean",
        "is_bonus"            => "boolean",
        "allowed_media_types" => EnumSetCast::class . ":" . MediaType::class,
        "allows_audio"        => "boolean",
    ];

    public string $impressions_models_pivot_table = "products_impressions_models";

    protected function getPublicRelations() {
        return [
            "attachments"         => "attachments",
            "category"            => "category",
            "category-format"     => "category.format",
            "format"              => "format",
            "impressions_models"  => ["impressions_models", "category.impressions_models"],
            "inventories"         => Relation::make(
                load: ["inventory_resource.inventories_settings", "inventory_resource.external_representations"],
                gate: Capability::properties_inventories_view
            ),
            "locations"           => "locations",
            "loop_configurations" => ["loop_configurations", "category.loop_configurations"],
            "pricelist"           => ["load:pricelist.categories_pricings", "load:pricelist.products_pricings"],
            "property"            => "property",
            "unavailabilities"    => Relation::make(
                load: ["unavailabilities.translations", "unavailabilities.products"],
                gate: Capability::properties_unavailabilities_view
            ),
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
        return $this->belongsToMany(Unavailability::class, "products_unavailabilities", "product_id", "unavailability_id");
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Impressions Models
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

    /*
    |--------------------------------------------------------------------------
    | Loop Configuration
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Inventories
    |--------------------------------------------------------------------------
    */

    public function getEnabledInventoriesAttribute() {
        /** @var Collection<ResourceInventorySettings> $propertyInventories */
        $propertyInventories = $this->property->inventories_settings()->where("is_enabled", "=", true)->get();

        /** @var Collection<ResourceInventorySettings> $productInventories */
        $productInventories = $this->inventories_settings;

        $disabledInventoriesID = $productInventories->where("is_enabled", "=", false)->pluck("inventory_id");

        return $productInventories->where("is_enabled", "=", true)
                                  ->pluck("inventory_id")
                                  ->merge(
                                      $propertyInventories->whereNotIn("inventory_id", $disabledInventoriesID)
                                                          ->pluck("inventory_id")
                                  );
    }

    public function toResource(int $inventoryID): ProductResource {
        /** @var ExternalInventoryResource|null $propertyId */
        $propertyId = $this->property->external_representations()
                                     ->withoutTrashed()
                                     ->firstWhere("inventory_id", "=", $inventoryID);

        /** @var ExternalInventoryResource|null $categoryId */
        $categoryId = $this->category->external_representations()
                                     ->withoutTrashed()
                                     ->firstWhere("inventory_id", "=", $inventoryID);

        /** @var Product|null $linkedProduct */
        $linkedProduct = $this->linked_product_id ? Product::find($this->linked_product_id) : null;
        /** @var ExternalInventoryResource|null $linkedProductId */
        $linkedProductId = $linkedProduct?->external_representations()
                                         ->withoutTrashed()
                                         ->firstWhere("inventory_id", "=", $inventoryID);

        $pricing = ProductPricing::make($this);

        /** @var Format|null $format */
        $format = $this->category->type === ProductType::Digital ? ($this->format ?? $this->category->format) : null;

        /** @var Layout|null $layout */
        $layout = $format->layouts->first(fn(Layout $layout) => $layout->frames->count() === 1);

        /** @var LoopConfiguration|null $loopConfiguration */
        $loopConfiguration = $format->loop_configurations()->first();

        $weeklyTraffic = ceil(collect($this->property->traffic->getRollingWeeklyTraffic())->sum() / 53);

        return new ProductResource(
            name               : LocalizedString::collection([
                                                                 new LocalizedString(locale: 'en-CA', value: trim($this->name_en)),
                                                                 new LocalizedString(locale: 'fr-CA', value: trim($this->name_fr)),
                                                             ]),
            type               : $this->category->type,
            category_id        : $categoryId?->toInventoryResourceId(),
            is_sellable        : $this->is_sellable,
            is_bonus           : $this->is_bonus,
            linked_product_id  : $linkedProductId?->toInventoryResourceId(),
            quantity           : $this->quantity,
            price_type         : $pricing->getType(),
            price              : $pricing->getPrice(),
            picture_url        : null,
            loop_configuration : $loopConfiguration ?
                                     new \Neo\Modules\Properties\Services\Resources\LoopConfiguration(
                                         loop_length_ms: $loopConfiguration->loop_length_ms,
                                         spot_length_ms: $loopConfiguration->spot_length_ms,
                                     ) : null,
            screen_width_px    : $layout?->frames->first()->width,
            screen_height_px   : $layout?->frames->first()->height,
            allowed_media_types: count($this->allowed_media_types) > 0 ? $this->allowed_media_types : $this->category->allowed_media_types,
            allows_audio       : $this->allows_audio !== null ? $this->allows_audio : $this->category->allows_audio,
            property_id        : $propertyId?->toInventoryResourceId(),
            property_name      : $this->property->actor->name,
            address            : $this->property->address->toInventoryResource(),
            geolocation        : new Geolocation(
                                     longitude: $this->property->address->geolocation->getLng(),
                                     latitude : $this->property->address->geolocation->getLat(),
                                 ),
            timezone           : $this->property->address?->timezone,
            operating_hours    : DayOperatingHours::collection($this->property->opening_hours->map(fn(OpeningHours $hours) => $hours->toInventoryResource())),
            weekly_traffic     : $weeklyTraffic,
            product_connect_id : $this->getKey(),
            property_connect_id: $this->property_id,
        );
    }
}
