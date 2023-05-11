<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResource.php
 */

namespace Neo\Modules\Properties\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\SecuredModel;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Properties\Rules\AccessibleInventoryResource;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;

/**
 * @property int                                   $id
 * @property InventoryResourceType                 $type
 *
 * @property Collection<ResourceInventorySettings> $inventories_settings
 * @property Collection<ExternalInventoryResource> $external_representations
 * @property Collection<number>                    $enabled_inventories
 * @property Collection<Product>                   $products
 */
class InventoryResource extends SecuredModel {
    use HasPublicRelations;

    protected $table = "inventory_resources";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        "type",
    ];

    protected $casts = [
        "type" => InventoryResourceType::class,
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleInventoryResource::class;

    public function getPublicRelations(): array {
        return [
            "inventories_settings"     => "inventories_settings",
            "external_representations" => "external_representations",
            "events"                   => "events",
        ];
    }

    public function resolveChildRouteBinding($childType, $value, $field) {
        return match ($childType) {
            "inventorySettings"      => $this->inventories_settings()->where("inventory_id", "=", $value)->firstOrFail(),
            "externalRepresentation" => $this->external_representations()->findOrFail($value),
            default                  => null,
        };
    }

    /**
     * @return HasMany<ResourceInventorySettings>
     */
    public function inventories_settings(): HasMany {
        return $this->hasMany(ResourceInventorySettings::class, "resource_id", "id");
    }

    public function external_representations(): HasMany {
        return $this->hasMany(ExternalInventoryResource::class, "resource_id", "id")->withTrashed();
    }

    public function events(): HasMany {
        return $this->hasMany(InventoryResourceEvent::class, "resource_id", "id");
    }

    /**
     * List all inventories with which this resource is enabled
     */
    public function getEnabledInventoriesAttribute() {
        return match ($this->type) {
            InventoryResourceType::Property        => $this->inventories_settings()
                                                           ->where("is_enabled", "=", true)
                                                           ->pluck("inventory_id"),
            InventoryResourceType::Product         => Product::query()
                                                             ->firstWhere("inventory_resource_id", "=", $this->id)->enabled_inventories,
            InventoryResourceType::ProductCategory => throw new Exception('To be implemented'),
        };
    }

    /**
     * A Inventory resource can be a product or a property.
     * Since our inventory system works with products, this method gets us which products are implied by this id.
     * For ID for a specific product, this method will just return a collection with one value, for ID for property,
     * this method will list all the products of the property
     *
     * @throws Exception
     */
    public function getProductsAttribute() {
        return match ($this->type) {
            InventoryResourceType::Property        => Property::query()
                                                              ->firstWhere("inventory_resource_id", "=", $this->id)->products ?? new Collection(),
            InventoryResourceType::Product         => Collection::make([Product::query()
                                                                               ->where("inventory_resource_id", "=", $this->id)
                                                                               ->first()]),
            InventoryResourceType::ProductCategory => throw new Exception('To be implemented'),
        };
    }
}
