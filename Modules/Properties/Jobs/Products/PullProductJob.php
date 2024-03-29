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

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Database\Eloquent\Builder;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Province;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Exceptions\Synchronization\MissingExternalInventoryResourceException;
use Neo\Modules\Properties\Exceptions\Synchronization\PropertyIDInconsistencyException;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Jobs\InventoryJobBase;
use Neo\Modules\Properties\Jobs\InventoryJobType;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyType;
use Neo\Modules\Properties\Models\ScreenType;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

class PullProductJob extends InventoryJobBase implements ShouldBeUniqueUntilProcessing {
    /**
     * @param int                      $resourceID Inventory Resource id of the product
     * @param int                      $inventoryID
     * @param IdentifiableProduct|null $externalProduct
     */
    public function __construct(
        private readonly int                      $resourceID,
        private readonly int                      $inventoryID,
        private readonly IdentifiableProduct|null $externalProduct = null,
    ) {
        parent::__construct(InventoryJobType::Pull, $this->resourceID, $this->inventoryID);
    }

    public function uniqueId() {
        return "$this->resourceID-$this->inventoryID";
    }

    /**
     * @return mixed
     * @throws InvalidInventoryAdapterException
     * @throws MissingExternalInventoryResourceException
     * @throws PropertyIDInconsistencyException
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

        // Get the product from the external inventory. If an external product was provided when dispatching the job, use that instead
        /** @var IdentifiableProduct $externalProduct */
        $externalProduct = $this->externalProduct ?? $inventory->getProduct($productExternalRepresentation->toInventoryResourceId());

        // If the product has a property_id, and the property already has one for this inventory, we make sure they match.
        /** @var InventoryResourceId|null $propertyExternalId */
        $propertyExternalId = $property->external_representations()
                                       ->where("inventory_id", "=", $this->inventoryID)
                                       ->first()
                                       ?->toInventoryResourceId();

        // Validate property ids
        if ($inventory->hasCapability(InventoryCapability::PropertiesRead)) {
            // If the product has a property ID, but the property has not, we register it.
            // If the product doesn't have a property ID but the property has one, that's UB
            // If the product has a property ID that is different from the one of the property we check if another property has the same id:
            //      if yes, we move the product, if not, we cancel the pull
            if ($propertyExternalId === null && $externalProduct->product->property_id !== null) {
                $externalRepresentation              = ExternalInventoryResource::fromInventoryResource($externalProduct->product->property_id);
                $externalRepresentation->resource_id = $property->inventory_resource_id;
                $externalRepresentation->save();
            }

            if ($propertyExternalId !== null
                && $externalProduct->product->property_id !== null
                && $propertyExternalId->external_id !== $externalProduct->product->property_id->external_id) {
                // We want to check if another property in Connect matches the id
                $property = Property::query()
                                    ->whereHas("external_representations", function (Builder $query) use ($externalProduct) {
                                        $query->where("inventory_id", "=", $this->inventoryID)
                                              ->where("external_id", "=", $externalProduct->product->property_id->external_id);
                                    })
                                    ->first();

                if ($property) {
                    // We have a property, move the product
                    $product->property_id = $property->getKey();
                } else {
                    throw new PropertyIDInconsistencyException($this->resourceID, $this->inventoryID, $externalProduct->product->property_id, $propertyExternalId);
                }
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

        $product->is_sellable = $externalProduct->product->is_sellable;

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
            $product->quantity = max(1, $externalProduct->product->quantity);
        }

        // Product media types
        if ($inventory->hasCapability(InventoryCapability::ProductsMediaTypes) && count($product->allowed_media_types) > 0) {
            $product->allowed_media_types = $externalProduct->product->allowed_media_types;
        }

        // Product audio support
        if ($inventory->hasCapability(InventoryCapability::ProductsAudioSupport) && $product->allows_audio !== null) {
            $product->allows_audio = $externalProduct->product->allows_audio;
        }

        // Product audio support
        if ($inventory->hasCapability(InventoryCapability::ProductsMotionSupport) && $product->allows_motion !== null) {
            $product->allows_motion = $externalProduct->product->allows_motion;
        }

        // Product screen size support
        if ($inventory->hasCapability(InventoryCapability::ProductsScreenSize) && $product->screen_size_in !== null) {
            $product->screen_size_in = $externalProduct->product->screen_size_in;
        }

        // Product screen type support
        if ($inventory->hasCapability(InventoryCapability::ProductsScreenType) && $product->screen_type_id !== null) {
            /** @var ScreenType|null $screenType */
            $screenType = ScreenType::query()->whereHas("external_representations", function (Builder $query) use ($inventory) {
                $query->where("inventory_id", "=", $inventory->getInventoryID());
            })->first();

            $product->screen_type_id = $screenType->getKey() ?? $product->screen_type_id;
        }

        if ($externalProduct->product->price_type === PriceType::Unit) {
            $product->unit_price = $externalProduct->product->price;
        } else if ($externalProduct->product->price_type === PriceType::CPM) {
            if ($product->category->programmatic_price !== $externalProduct->product->programmatic_price) {
                $product->programmatic_price = $externalProduct->product->programmatic_price;
            }
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

        // Property type
        if ($inventory->hasCapability(InventoryCapability::PropertiesType)) {
            /** @var PropertyType|null $propertyType */
            $propertyType = PropertyType::query()
                                        ->whereHas("external_representations", function (Builder $query) use ($inventory) {
                                            $query->where("inventory_id", "=", $inventory->getInventoryID());
                                        })
                                        ->first();

            if ($propertyType) {
                // If the product has a site type that is different of the received one
                if ($product->site_type_id !== null && $product->site_type_id !== $propertyType->getKey()) {
                    // If the property type is set and is the same, set the product type to null
                    if ($property->type_id === $propertyType->getKey()) {
                        $product->site_type_id = null;
                    } else {
                        $product->site_type_id = $propertyType->getKey();
                    }
                } else if ($product->site_type_id === null && $property->type_id !== $property->getKey()) {
                    // If the product has no site type, and the property type is different
                    $product->site_type_id = $propertyType->getKey();
                }
            }
        }


        // Property Name
        // To be decided

        // Property Address
        if ($externalProduct->product->address !== null) {
            /** @var Address $address */
            $address         = $property->address()->firstOrNew();
            $address->line_1 = $externalProduct->product->address->line_1;
            $address->line_2 = $externalProduct->product->address->line_2;

            /** @var City|null $city */
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

            if ($externalProduct->product->geolocation) {
                $geolocation = new Point($externalProduct->product->geolocation->latitude, $externalProduct->product->geolocation->longitude);
                if ((string)$address->geolocation !== (string)$geolocation) {
                    $address->geolocation = $geolocation;
                }
            }

            $address->save();

            $property->address_id = $address->getKey();
        }


        $product->save();
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
