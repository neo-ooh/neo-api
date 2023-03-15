<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullProductJob.php
 */

namespace Neo\Modules\Properties\Jobs\Products;

use Edujugon\Laradoo\Exceptions\OdooException;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Database\Eloquent\Builder;
use JsonException;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Province;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Exceptions\Synchronization\MissingExternalInventoryResourceException;
use Neo\Modules\Properties\Exceptions\Synchronization\PropertyIDInconsistencyException;
use Neo\Modules\Properties\Exceptions\Synchronization\PullNotAllowedException;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Jobs\InventoryJobBase;
use Neo\Modules\Properties\Jobs\InventoryJobType;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

class PullProductJob extends InventoryJobBase implements ShouldBeUniqueUntilProcessing {
    public function __construct(
        private readonly int $resourceID,
        private readonly int $inventoryID
    ) {
        parent::__construct(InventoryJobType::Pull, $this->resourceID, $this->inventoryID);
    }

    public function uniqueId() {
        return "$this->resourceID-$this->inventoryID";
    }

    /**
     * @throws InvalidInventoryAdapterException
     * @throws OdooException
     * @throws PullNotAllowedException
     * @throws JsonException
     * @throws PropertyIDInconsistencyException
     * @throws MissingExternalInventoryResourceException
     * @throws UnsupportedInventoryFunctionalityException
     */
    public function run(): mixed {
        // To pull a product, we first need to check that the product synchronization with the specified inventory is enabled, and there's an external representation available.
        /** @var Product $product */
        $product = Product::query()
                          ->withTrashed()
                          ->with("property")
                          ->where("inventory_resource_id", "=", $this->resourceID)
                          ->firstOrFail();
        /** @var Property $property */
        $property = $product->property;

        // Validate the product has an id for this inventory
        /** @var ExternalInventoryResource|null $productExternalRepresentation */
        $productExternalRepresentation = $product->external_representations()
                                                 ->withoutTrashed()
                                                 ->where("inventory_id", "=", $this->inventoryID)
                                                 ->first();

        if (!$productExternalRepresentation) {
            // No representation available for product for this inventory, stop here
            throw new MissingExternalInventoryResourceException($this->resourceID, $this->inventoryID);
        }

        // Product can be pulled and has an ID. Get an inventory instance and do it
        $inventoryProvider = InventoryProvider::findOrFail($this->inventoryID);

        /** @var InventoryAdapter $inventory */
        $inventory = InventoryAdapterFactory::make($inventoryProvider);

        if (!$inventory->hasCapability(InventoryCapability::ProductsRead)) {
            // Inventory does not support reading products, stop here.
            throw new UnsupportedInventoryFunctionalityException($this->inventoryID, InventoryCapability::ProductsRead);
        }

        // Get the product from the external inventory
        /** @var IdentifiableProduct $externalProduct */
        $externalProduct = $inventory->getProduct($productExternalRepresentation->toInventoryResourceId());

        // If the product has a property_id, and the property already has one for this inventory, we make sure they match.
        /** @var InventoryResourceId|null $propertyExternalId */
        $propertyExternalId = $property->external_representations()
                                       ->where("inventory_id", "=", $this->inventoryID)
                                       ->first()
                                       ?->toInventoryResourceId();

        // Validate property ids
        if ($inventory->hasCapability(InventoryCapability::PropertiesRead)) {
            // If the product has a property ID, but the property has not, we make register it.
            // If the product doesn't have a property ID but the property has one, that's UB
            // If the product has a property ID that is different from the one of the property, we cancel the pull
            if ($propertyExternalId === null && $externalProduct->product->property_id !== null) {
                $externalRepresentation              = ExternalInventoryResource::fromInventoryResource($externalProduct->product->property_id);
                $externalRepresentation->resource_id = $property->inventory_resource_id;
                $externalRepresentation->save();
            }

            if ($propertyExternalId !== null
                && $externalProduct->product->property_id !== null
                && $propertyExternalId->external_id !== $externalProduct->product->property_id->external_id) {
                throw new PropertyIDInconsistencyException($this->resourceID, $this->inventoryID, $externalProduct->product->property_id, $propertyExternalId);
            }

        }
        // Using the external product data, we update our own product and property.
        // Product name
        foreach ($externalProduct->product->name as $localizedName) {
            switch ($localizedName->locale) {
                case "fr-CA":
                    $product->name_fr = $localizedName->value;
                    break;
                case "en-CA":
                    $product->name_en = $localizedName->value;
                    break;
            }
        }

        // Product category and type
        if ($externalProduct->product->category_id !== null) {
            // Try to load a product category matching the given id
            /** @var ProductCategory|null $productCategory */
            $productCategory = ProductCategory::query()
                                              ->whereHas("external_representations", function (Builder $query) use ($externalProduct) {
                                                  $query->where("inventory_id", "=", $this->inventoryID)
                                                        ->where("external_id", "=", $externalProduct->product->category_id->external_id);
                                              })
                                              ->first();

            if (!$productCategory) {
                // Product category could not be found, create it
                $productCategory = $this->importProductCategory(
                    $inventory,
                    $externalProduct->product->category_id,
                    $externalProduct->product->type,
                );
            }

            // Assign the product to the category
            $product->category_id = $productCategory->getKey();
        }

        // If the product has no category id, and the inventory supports categories, that means the missing category is deliberate
        if ($externalProduct->product->category_id === null && $inventory->hasCapability(InventoryCapability::ProductCategoriesRead)) {
            $product->category_id = null;
        }

        // Product bonus
        $product->is_bonus = $externalProduct->product->is_bonus;

        // Product Specs
        if ($inventory->hasCapability(InventoryCapability::ProductsQuantity)) {
            $product->quantity = $externalProduct->product->quantity;
        }

        if ($externalProduct->product->price_type === PriceType::Unit) {
            $product->unit_price = $externalProduct->product->price;
        }

        // Linked product
        if ($externalProduct->product->linked_product_id !== null) {
            // This product references another product as sharing inventory. If this product already exist in our system, we will link them,
            // Otherwise, the other product will do it whn it gets imported
            $linkedProduct = Product::query()
                                    ->whereHas("external_representations", function (Builder $query) use ($externalProduct) {
                                        $query->where("inventory_id", "=", $this->inventoryID)
                                              ->where("external_id", "=", $externalProduct->product->linked_product_id->external_id);
                                    })
                                    ->first();

            $product->linked_product_id = $linkedProduct?->getKey();

            // Set the reverse link on the linked product
            if ($linkedProduct) {
                $linkedProduct->linked_product_id = $product->getKey();
                $linkedProduct->save();
            }
        } else {
            $product->linked_product_id = null;
        }

        $product->save();

        // Property Name
        // To be decided

        // Property Address
        if ($externalProduct->product->address !== null) {
            /** @var Address $address */
            $address         = $property->address()->firstOrNew();
            $address->line_1 = $externalProduct->product->address->line_1;
            $address->line_2 = $externalProduct->product->address->line_2;

            $city = City::query()
                        ->where("name", "=", $externalProduct->product->address->city->name)
                        ->whereHas("province", fn(Builder $query) => $query->where("slug", "=", $externalProduct->product->address->city->province_slug)
                        )->first();

            if (!$city) {
                $city              = new City();
                $city->name        = $externalProduct->product->address->city->name;
                $city->province_id = Province::query()
                                             ->firstWhere("slug", "=", $externalProduct->product->address->city->province_slug)
                                             ->getKey();
                $city->save();
            }

            $address->city_id = $city->getKey();
            $address->zipcode = str_replace(" ", "", $externalProduct->product->address->zipcode);

            $geolocation = new Point($externalProduct->product->geolocation->latitude, $externalProduct->product->geolocation->longitude);
            if ((string)$address->geolocation !== (string)$geolocation) {
                $address->geolocation = $geolocation;
            }

            $address->save();

            $property->address_id = $address->getKey();
        }

        $property->save();

        // Pull is complete
        return [];
    }

    /**
     * Load the specified category from the inventory and stores it in Connect.
     *
     * @param InventoryAdapter    $inventory
     * @param InventoryResourceId $categoryId
     * @param ProductType         $type
     * @return ProductCategory
     */
    protected function importProductCategory(InventoryAdapter $inventory, InventoryResourceId $categoryId, ProductType $type): ProductCategory {
        $externalCategory = $inventory->getProductCategory($categoryId);

        $category          = new ProductCategory();
        $category->type    = $type;
        $category->name_en = $externalCategory->category_name;
        $category->name_fr = $externalCategory->category_name;
        $category->save();

        $category->external_representations()->insert([
                                                          "resource_id"  => $category->inventory_resource_id,
                                                          "inventory_id" => $inventory->getInventoryID(),
                                                          "type"         => InventoryResourceType::ProductCategory,
                                                          "external_id"  => $externalCategory->category_id->external_id,
                                                          "context"      => json_encode($externalCategory->category_id->context),
                                                      ]);

        return $category;
    }
}
