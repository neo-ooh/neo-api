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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * @property int                           $id
 * @property int                           $inventory_resource_id
 * @property int                           $property_id
 * @property int|null                      $category_id
 * @property string                        $name_en
 * @property string                        $name_fr
 * @property int|null                      $format_id
 * @property int|null                      $site_type_id
 * @property int                           $quantity
 * @property boolean                       $is_sellable
 * @property double                        $unit_price
 * @property boolean                       $is_bonus
 * @property int|null                      $linked_product_id
 * @property MediaType[]                   $allowed_media_types
 * @property boolean|null                  $allows_audio
 * @property boolean                       $allows_motion
 * @property double|null                   $production_cost
 * @property double|null                   $programmatic_price
 * @property string                        $notes
 * @property double|null                   $screen_size_in
 * @property int|null                      $screen_type_id
 * @property Carbon                        $created_at
 * @property Carbon                        $updated_at
 * @property Carbon                        $deleted_at
 *
 * @property Property                      $property
 * @property ProductCategory               $category
 * @property Format                        $format
 * @property PropertyType                  $site_type
 * @property ProductWarnings               $warnings
 * @property Collection<ImpressionsModel>  $impressions_models
 * @property Collection<LoopConfiguration> $loop_configurations
 * @property Collection<Unavailability>    $unavailabilities
 * @property ScreenType                    $screen_type
 *
 * @property Pricelist|null                $pricelist
 * @property PricelistProduct|null         $pricing
 *
 * @property Collection<number>            $enabled_inventories
 *
 * @property int                           $locations_count           // Laravel `withCount` result accessor
 * @property Collection<Location>          $locations
 *
 * @property int|null                      $cover_picture_id
 * @property InventoryPicture|null         $cover_picture
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
        "programmatic_price",
    ];

    protected $casts = [
        "is_sellable"         => "boolean",
        "is_bonus"            => "boolean",
        "allowed_media_types" => EnumSetCast::class . ":" . MediaType::class,
        "allows_audio"        => "boolean",
        "allows_motion"       => "boolean",
    ];

    public string $impressions_models_pivot_table = "products_impressions_models";

    protected function getPublicRelations() {
        return [
            "attachments"         => "attachments",
            "category"            => "category",
            "category.format"     => "category.format",
            "cover_picture"       => Relation::make(
                load: "cover_picture",
                gate: Capability::properties_pictures_view,
            ),
            "format"              => "format",
            "impressions_models"  => ["impressions_models", "category.impressions_models"],
            "inventories"         => Relation::make(
                load: ["inventory_resource.inventories_settings", "inventory_resource.external_representations"],
                gate: Capability::properties_inventories_view
            ),
            "locations"           => "locations",
            "loop_configurations" => ["loop_configurations", "category.loop_configurations"],
            "pictures"            => Relation::make(
                load: "pictures",
                gate: Capability::properties_pictures_view
            ),
            "pricelist"           => ["load:pricelist.categories_pricings", "load:pricelist.products_pricings"],
            "property"            => "property",
            "screen_type"         => ["screen_type", "category.screen_type"],
            "site_type"           => ["site_type", "property.type"],
            "unavailabilities"    => Relation::make(
                load: ["unavailabilities.translations", "unavailabilities.products"],
                gate: Capability::properties_unavailabilities_view
            ),
            "warnings"            => Relation::make(
                load: "warnings",
                gate: Capability::products_edit
            ),
        ];
    }

    protected static function boot(): void {
        parent::boot();

        static::deleting(static function (Product $product) {
            $product->attachments->each(fn(Attachment $attachment) => $attachment->delete());
            $product->unavailabilities->each(fn(Unavailability $unavailability) => $unavailability->delete());
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
//                    ->withTrashed();
    }

    public function format(): BelongsTo {
        return $this->belongsTo(Format::class, "format_id", "id");
    }

    public function site_type(): BelongsTo {
        return $this->belongsTo(PropertyType::class, "site_type_id", "id");
    }

    public function category(): BelongsTo {
        return $this->belongsTo(ProductCategory::class, "category_id", "id");
    }

    public function warnings(): HasOne {
        return $this->hasOne(ProductWarnings::class, "product_id", "id");
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

    public function screen_type(): BelongsTo {
        return $this->belongsTo(ScreenType::class, "screen_type_id", "id");
    }

    public function pictures(): HasMany {
        return $this->hasMany(InventoryPicture::class, "product_id", "id")->orderBy("order");
    }

    public function cover_picture(): BelongsTo {
        return $this->belongsTo(InventoryPicture::class, "cover_picture_id", "id");
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

    /**
     * @param Carbon $date
     * @return ImpressionsModel|null
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

    /**
     * Gives how many impressions per spot will be generated by each play for the given weekly traffic
     *
     * @param $weekTraffic
     * @return array<int, float> A 1-indexed array of the amount of impressions generated on each day for one spot on one play
     */
    public function getSpotImpressionsForWeek($weekTraffic) {
        $impressionsModel  = $this->getImpressionModel(Carbon::now());
        $loopConfiguration = $this->getLoopConfiguration(Carbon::now());

        // Stop if we're missing some infos
        if (!$impressionsModel || !$loopConfiguration) {
            return array_fill(1, 7, 0);
        }

        // Start by computing the open length in hours for each day of the week
        /** @var array<int, float> $openingLengthHours How many hour the property is open on each day of the week */
        $openingLengthHours = [];

        foreach (range(1, 7) as $weekday) {
            $dayOpeningHours = $this->property->opening_hours->firstWhere("weekday", "=", $weekday) ??
                new OpeningHours([
                                     "weekday"   => $weekday,
                                     "is_closed" => false,
                                     "open_at"   => "00:00",
                                     "close_at"  => "23:59",
                                 ]);

            $openLengthHours = $dayOpeningHours->is_closed ? 0 : $dayOpeningHours->open_at->diffInMinutes($dayOpeningHours->close_at, true) / 60;

            $openingLengthHours[$weekday] = $openLengthHours;
        }

        // Get the median amount of traffic for one hour
        $trafficPerHour = $weekTraffic / collect($openingLengthHours)->values()->sum();

        // Get the amount of impressions generated by one sport over the course of one hour
        $el                        = new ExpressionLanguage();
        $impressionsPerSpotPerHour = $el->evaluate($impressionsModel->formula, array_merge(
            [
                "traffic" => $trafficPerHour, "faces" => $this->quantity, "spots" => 1, "loopLengthMin" => $loopConfiguration->loop_length_ms / (1_000 * 60), // ms to minutes
            ], $impressionsModel->variables));

        $spotImpressionsPerPlay = [];

        foreach ($openingLengthHours as $weekday => $openLengthHrs) {
            $loopLengthMin                    = $loopConfiguration->loop_length_ms / (60_000);
            $loopsPerDay                      = ($openLengthHrs * 60 /* Hrs to minutes */) / $loopLengthMin;
            $spotImpressionsPerDay            = $impressionsPerSpotPerHour * $openLengthHrs;
            $spotImpressionsPerPlay[$weekday] = $spotImpressionsPerDay / $loopsPerDay;
        }

        return $spotImpressionsPerPlay;
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
        $this->loadMissing([
                               "property",
                               "property.type.external_representations",
                               "category.format",
                               "locations",
                           ]);
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
        $layout = $format->main_layout ?? $format?->layouts->first(fn(Layout $layout) => $layout->frames->count() === 1);

        $loopConfiguration = $this->getLoopConfiguration(Carbon::now());

        $weeklyTraffic = ceil(collect($this->property->traffic->getRollingWeeklyTraffic())->sum() / 53);


        return new ProductResource(
            name                     : LocalizedString::collection([
                                                                       new LocalizedString(locale: 'en-CA', value: trim($this->name_en)),
                                                                       new LocalizedString(locale: 'fr-CA', value: trim($this->name_fr)),
                                                                   ]),
            type                     : $this->category->type,
            category_id              : $categoryId?->toInventoryResourceId(),
            is_sellable              : $this->is_sellable,
            is_bonus                 : $this->is_bonus,
            linked_product_id        : $linkedProductId?->toInventoryResourceId(),
            quantity                 : $this->quantity,
            price_type               : $pricing->getType(),
            price                    : $pricing->getPrice(),
            programmatic_price       : $this->programmatic_price ?? $this->category->programmatic_price,
            picture_url              : null,
            loop_configuration       : $loopConfiguration ?
                                           new \Neo\Modules\Properties\Services\Resources\LoopConfiguration(
                                               loop_length_ms: $loopConfiguration->loop_length_ms,
                                               spot_length_ms: $loopConfiguration->spot_length_ms,
                                           ) : null,
            screen_width_px          : $layout?->frames->first()->width,
            screen_height_px         : $layout?->frames->first()->height,
            screen_size_in           : $this->screen_size_in ?? $this->category->screen_size_in,
            screen_type              : ($this->screen_type ?? $this->category->screen_type)?->external_representations
                                           ->firstWhere("inventory_id", "=", $inventoryID)
                                           ?->toInventoryResourceId(),
            allowed_media_types      : count($this->allowed_media_types) > 0 ? $this->allowed_media_types : $this->category->allowed_media_types,
            allows_audio             : $this->allows_audio !== null ? $this->allows_audio : $this->category->allows_audio,
            allows_motion            : $this->allows_motion !== null ? $this->allows_motion : $this->category->allows_motion,
            property_id              : $propertyId?->toInventoryResourceId(),
            property_name            : $this->property->actor->name,
            property_type            : ($this->site_type ?? $this->property->type)?->external_representations->firstWhere("inventory_id", "=", $inventoryID)
                                                                                                             ?->toInventoryResourceId(),
            address                  : $this->property->address->toInventoryResource(),
            geolocation              : new Geolocation(
                                           longitude: $this->property->address->geolocation->getCoordinates()[0],
                                           latitude : $this->property->address->geolocation->getCoordinates()[1],
                                       ),
            timezone                 : $this->property->address?->timezone,
            operating_hours          : DayOperatingHours::collection(collect(range(1, 7))->map(fn($weekday) => $this->property->opening_hours->firstWhere("weekday", $weekday)
                                                                                                                                             ?->toInventoryResource()
                                           ?? new DayOperatingHours($weekday, false, "00:00", "23:59", 24 * 60 - 1)
                                       )),
            weekly_traffic           : $weeklyTraffic,
            weekdays_spot_impressions: $this->getSpotImpressionsForWeek($weeklyTraffic),
            product_connect_id       : $this->getKey(),
            property_connect_id      : $this->property_id,
            broadcastLocations       : $this->locations->load("players")
                                                       ->map(fn(Location $location) => $location->toInventoryResource())
                                                       ->all(),
            externalRepresentations  : $this->external_representations->map(fn(ExternalInventoryResource $resource) => $resource->toInventoryResourceId())
                                                                      ->all()
        );
    }
}
