<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Property.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Neo\Enums\Capability;
use Neo\Helpers\Relation;
use Neo\Models\Actor;
use Neo\Models\Address;
use Neo\Models\SecuredModel;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Models\Traits\HasView;
use Neo\Modules\Demographics\Models\IndexSet;
use Neo\Modules\Properties\Models\Traits\InventoryResourceModel;
use Neo\Modules\Properties\Rules\AccessibleProperty;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Throwable;

/**
 * Class Property
 *
 * @property int                                   $actor_id
 * @property-read int                              $name
 * @property int                                   $inventory_resource_id
 * @property int                                   $address_id
 * @property int                                   $network_id
 * @property int|null                              $pricelist_id
 * @property int|null                              $type_id
 * @property boolean                               $is_sellable
 * @property boolean                               $has_tenants
 * @property string                                $website
 * @property int                                   $mobile_impressions_per_week
 *
 * @property Carbon                                $last_review_at
 * @property string                                $notes
 * @property-read int                              $demographic_variables_count
 *
 * @property Date                                  $created_at
 * @property int|null                              $created_by
 * @property Date                                  $updated_at
 * @property int|null                              $updated_by
 * @property Date|null                             $deleted_at
 * @property int|null                              $deleted_by
 *
 * @property-read Actor                                 $actor
 * @property-read Collection<PropertyTranslation>       $translations
 * @property-read PropertyWarnings                      $warnings
 * @property-read PropertyTrafficSettings               $traffic
 * @property-read PropertyType|null                     $type
 * @property-read Address|null                          $address
 * @property-read PropertyNetwork|null                  $network
 * @property-read Collection<InventoryPicture>          $pictures
 * @property-read Collection<PropertyFieldSegmentValue> $fields_values
 * @property-read Collection<OpeningHours>              $opening_hours
 * @property-read Collection<Brand>                     $tenants
 * @property-read Pricelist                             $pricelist
 * @property-read Collection<Actor>                     $contacts
 * @property-read Collection<Unavailability>            $unavailabilities
 * @property-read Collection<IndexSet>            $index_sets
 *
 * @property-read Collection<Product>                   $products
 *
 * @property-read array                                 $rolling_weekly_traffic
 *
 * @property int|null                              $cover_picture_id
 * @property-read InventoryPicture|null                 $cover_picture
 *
 * @property-read InventoryResource                     $inventoryResource
 */
class Property extends SecuredModel {
	use HasPublicRelations;
	use HasCreatedByUpdatedBy;
	use InventoryResourceModel;
	use HasView;

	/*
	|--------------------------------------------------------------------------
	| Table properties
	|--------------------------------------------------------------------------
	*/

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = "properties_view";

	/**
	 * The table used for write operations
	 *
	 * @var string
	 */
	public $write_table = "properties";

	/**
	 * The primary key for the table
	 *
	 * @var string
	 */
	protected $primaryKey = "actor_id";

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = false;

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	public $casts = [
		"is_sellable"    => "boolean",
		"has_tenants"    => "boolean",
		"last_review_at" => "datetime",
	];

	/**
	 * The rule used to validate access to the model upon binding it with a route
	 *
	 * @var string
	 */
	protected string $accessRule = AccessibleProperty::class;

	public $with = [
		"actor:id,name",
	];

	protected $touches = [
		"products",
	];

	public InventoryResourceType $inventoryResourceType = InventoryResourceType::Property;

	public function getPublicRelations(): array {
		return [
			"actor"                     => "load:actor",
			"address"                   => Relation::make(load: "address.city"),
			"contacts"                  => "load:contacts",
			"cover_picture"             => Relation::make(
				load: "cover_picture",
				gate: [Capability::properties_pictures_view, Capability::planner_access],
			),
			"demographic_values_count"  => Relation::make(
				count: "demographicValues",
				gate : [Capability::properties_demographics_view],
			),
			"fields"                    => ["network.properties_fields", "fields_values"],
			"fields_values"             => "load:fields_values",
			"inventories"               => Relation::make(
				load: ["inventory_resource.inventories_settings", "inventory_resource.external_representations"],
				gate: Capability::properties_inventories_view
			),
			"locations"                 => "load:actor.own_locations",
			"locations_ids"             => "actor.own_locations:id,network_id",
			"network"                   => "load:network",
			"opening_hours"             => "opening_hours",
			"parent"                    => "load:actor.parent",
			"pictures"                  => Relation::make(
				load: "pictures",
				gate: [Capability::properties_pictures_view, Capability::planner_access]
			),
			"pictures_products"         => Relation::make(
				load: "pictures.product",
				gate: [Capability::properties_pictures_view, Capability::planner_access]
			),
			"pricelist"                 => Relation::make(
				load: ["pricelist.categories_pricings", "pricelist.products_pricings"],
				gate: [Capability::properties_pricelist_view, Capability::planner_access]
			),
			"products"                  => Relation::make(
				load: "products",
				gate: [Capability::products_view, Capability::planner_access]
			),
			"products.warnings"         => Relation::make(
				load: "products.warnings",
				gate: Capability::products_edit,
			),
			"products_ids"              => Relation::make(
				load: "products:id",
				gate: Capability::products_view
			),
			"products.unavailabilities" => ["load:products.unavailabilities.translations", "load:products.unavailabilities.products"],
			"tags"                      => Relation::make(
				load: "actor.tags",
				gate: [Capability::properties_tags_view, Capability::planner_access]
			),
			"type"                      => Relation::make(
				load: "type"
			),
			"tenants"                   => Relation::make(
				load: "tenants",
				gate: [Capability::properties_tenants_view, Capability::planner_access]
			),
			"traffic.monthly_data"      => ["load:traffic.monthly_data"],
			"traffic.weekly_data"       => ["load:traffic.weekly_data"],
			"traffic.rolling_weekly"    => Relation::make(
				load  : 'traffic.weekly_data',
				custom: fn(Property $property) => $property->traffic->append("rolling_weekly_traffic"),
			),
			"traffic.source"            => ["load:traffic.source"],
			"translations"              => "translations",
			"unavailabilities"          => Relation::make(
				load: ["unavailabilities.translations", "unavailabilities.products"],
				gate: [Capability::properties_unavailabilities_view, Capability::planner_access]
			),
			"warnings"                  => Relation::make(
				load: "warnings"
			),
		];
	}

	protected static function boot(): void {
		parent::boot();

		static::deleting(static function (Property $property) {
			DB::beginTransaction();
			try {
				$property->pictures->each(fn($picture) => $picture->delete());

				$property->traffic()->delete();
				$property->fields_values()->delete();
				$property->demographicValues()->delete();
				$property->products()->delete();
				$property->opening_hours()->delete();
				$property->tenants()->detach();
				$property->contacts()->delete();

				DB::commit();
			} catch (Throwable $err) {
				DB::rollBack();
				throw $err;
			}
		});
	}

	/*
	|--------------------------------------------------------------------------
	| Relations
	|--------------------------------------------------------------------------
	*/

    /**
     * @return BelongsTo<Actor>
     */
	public function actor(): BelongsTo {
		return $this->belongsTo(Actor::class, "actor_id");
	}

    /**
     * @return BelongsTo<PropertyNetwork>
     */
	public function network(): BelongsTo {
		return $this->belongsTo(PropertyNetwork::class, "network_id");
	}

    /**
     * @return HasOne<PropertyWarnings>
     */
	public function warnings(): HasOne {
		return $this->hasOne(PropertyWarnings::class, "property_id", "actor_id");
	}

    /**
     * @return HasMany<PropertyTranslation>
     */
	public function translations(): HasMany {
		return $this->hasMany(PropertyTranslation::class, "property_id", "actor_id");
	}

    /**
     * @return HasOne<PropertyTrafficSettings>
     */
	public function traffic(): HasOne {
		return $this->hasOne(PropertyTrafficSettings::class, "property_id", "actor_id");
	}

    /**
     * @return BelongsTo<PropertyType>
     */
	public function type(): BelongsTo {
		return $this->belongsTo(PropertyType::class, "type_id", "id");
	}

    /**
     * @return BelongsTo<Address>
     */
	public function address(): BelongsTo {
		return $this->belongsTo(Address::class, "address_id", "id");
	}

    /**
     * @return HasMany<InventoryPicture>
     */
	public function pictures(): HasMany {
		return $this->hasMany(InventoryPicture::class, "property_id", "actor_id")->orderBy("order");
	}

    /**
     * @return BelongsTo<InventoryPicture>
     */
	public function cover_picture(): BelongsTo {
		return $this->belongsTo(InventoryPicture::class, "cover_picture_id", "id");
	}

    /**
     * @return HasMany<PropertyFieldSegmentValue>
     */
	public function fields_values(): HasMany {
		return $this->hasMany(PropertyFieldSegmentValue::class, "property_id", "actor_id");
	}

    /**
     * @return HasMany<Product>
     */
	public function products(): HasMany {
		return $this->hasMany(Product::class, "property_id", "actor_id");
	}

    /**
     * @return BelongsToMany<ProductCategory>
     */
	public function products_categories(): BelongsToMany {
		return $this->belongsToMany(ProductCategory::class, "products", "property_id", "category_id")
		            ->distinct();
	}

    /**
     * @return HasMany<OpeningHours>
     */
	public function opening_hours(): HasMany {
		return $this->hasMany(OpeningHours::class, "property_id")->orderBy("weekday");
	}

    /**
     * @return BelongsToMany<Brand>
     */
	public function tenants(): BelongsToMany {
		return $this->belongsToMany(Brand::class, "properties_tenants", "property_id", "brand_id");
	}

    /**
     * @return HasMany<DemographicValue>
     */
	public function demographicValues(): HasMany {
		return $this->hasMany(DemographicValue::class, "property_id", "actor_id");
	}

    /**
     * @return BelongsTo<Pricelist>
     */
	public function pricelist(): BelongsTo {
		return $this->belongsTo(Pricelist::class, "pricelist_id", "id");
	}

    /**
     * @return BelongsToMany<Actor>
     */
	public function contacts(): BelongsToMany {
		return $this->belongsToMany(Actor::class, "properties_contacts", "property_id", "actor_id")
		            ->with(["phone"])
		            ->withPivot(["role"])
		            ->as("contact");
	}

    /**
     * @return BelongsToMany<Unavailability>
     */
	public function unavailabilities(): BelongsToMany {
		return $this->belongsToMany(Unavailability::class, "properties_unavailabilities", "property_id", "unavailability_id");
	}

    /**
     * @return HasMany<IndexSet>
     */
    public function index_sets(): HasMany {
        return $this->hasMany(IndexSet::class, "property_id", "actor_id");
    }


	/*
	|--------------------------------------------------------------------------
	| Misc
	|--------------------------------------------------------------------------
	*/

	public function getTraffic(int $year, int $month): int|null {
		/** @var ?MonthlyTrafficDatum $traffic */
		$traffic = $this->traffic->data
			->where("year", "=", $year)
			->where("month", "=", $month)
			->first();

		if (!$traffic) {
			return null;
		}

		return $traffic->final_traffic;
	}
}
