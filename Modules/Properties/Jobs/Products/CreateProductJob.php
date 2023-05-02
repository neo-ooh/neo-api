<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreateProductJob.php
 */

namespace Neo\Modules\Properties\Jobs\Products;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Jobs\InventoryJobBase;
use Neo\Modules\Properties\Jobs\InventoryJobType;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;

class CreateProductJob extends InventoryJobBase implements ShouldBeUniqueUntilProcessing {
    public function __construct(
        private readonly int   $resourceID,
        private readonly int   $inventoryID,
        private readonly array $context
    ) {
        parent::__construct(InventoryJobType::Create, $this->resourceID, $this->inventoryID);
    }

    public function uniqueId() {
        return "$this->resourceID-$this->inventoryID";
    }

    /**
     * @return mixed
     * @throws InvalidInventoryAdapterException
     * @throws UnsupportedInventoryFunctionalityException
     */
    protected function run(): mixed {
        // Let's create the product in the specified inventory. For this, we build the product resource and pass it to the inventory
        /** @var Product $product */
        $product = Product::query()
                          ->withTrashed()
                          ->with(["property.traffic", "category.format", "format"])
                          ->where("inventory_resource_id", "=", $this->resourceID)
                          ->firstOrFail();

        // Product can be pulled and has an ID. Get an inventory instance and do it
        $inventoryProvider = InventoryProvider::findOrFail($this->inventoryID);

        /** @var InventoryAdapter $inventory */
        $inventory = InventoryAdapterFactory::make($inventoryProvider);

        if (!$inventory->hasCapability(InventoryCapability::ProductsWrite)) {
            // Inventory does not support reading products, stop here.
            throw new UnsupportedInventoryFunctionalityException($this->inventoryID, InventoryCapability::ProductsWrite);
        }

        // Get the product resource
        $productResource = $product->toResource($inventory->getInventoryID());

        // Replicate the product on the inventory
        $externalProductId = $inventory->createProduct($productResource, $this->context);

        // Store the product external ID
        $externalProductResource              = ExternalInventoryResource::fromInventoryResource($externalProductId);
        $externalProductResource->resource_id = $product->inventory_resource_id;
        $externalProductResource->save();

        if (!$inventory->hasCapability(InventoryCapability::PropertiesRead)) {
            // Inventory does not support property ids, stop here
            return [$externalProductResource];
        }

        // If the property has no inventory id for this, load the product from the inventory and store the inventory id
        if ($product->property->external_representations()
                              ->withoutTrashed()
                              ->where("inventory_id", "=", $this->inventoryID)
                              ->count() > 0
        ) {
            // Property already has an id, stop here
            return [$externalProductResource];
        }

        // Pull the product, and get the property id
        $externalProduct = $inventory->getProduct($externalProductId);

        $externalPropertyResource              = ExternalInventoryResource::fromInventoryResource($externalProduct->product->property_id);
        $externalPropertyResource->resource_id = $product->property->inventory_resource_id;
        $externalPropertyResource->save();

        // All done
        return [$externalPropertyResource];
    }
}
