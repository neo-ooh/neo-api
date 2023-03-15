<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PushProductJob.php
 */

namespace Neo\Modules\Properties\Jobs\Products;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Exceptions\Synchronization\MissingExternalInventoryResourceException;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Jobs\InventoryJobBase;
use Neo\Modules\Properties\Jobs\InventoryJobType;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

class PushProductJob extends InventoryJobBase implements ShouldBeUniqueUntilProcessing {
    public function __construct(
        private readonly int $resourceID,
        private readonly int $inventoryID
    ) {
        parent::__construct(InventoryJobType::Push, $this->resourceID, $this->inventoryID);
    }

    public function uniqueId() {
        return "$this->resourceID-$this->inventoryID";
    }

    /**
     * @return array
     * @throws InvalidInventoryAdapterException
     * @throws MissingExternalInventoryResourceException
     * @throws UnsupportedInventoryFunctionalityException
     */
    protected function run(): array {
        // To pull a product, we first need to check that the product synchronization with the specified inventory is enabled, and there's an external representation available.
        /** @var Product $product */
        $product = Product::query()
                          ->withTrashed()
                          ->with("property")
                          ->where("inventory_resource_id", "=", $this->resourceID)
                          ->firstOrFail();

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

        if (!$inventory->hasCapability(InventoryCapability::ProductsWrite)) {
            // Inventory does not support reading products, stop here.
            throw new UnsupportedInventoryFunctionalityException($this->inventoryID, InventoryCapability::ProductsRead);
        }

        // Get the product resource
        $productResource = $product->toResource($inventory->getInventoryID());

        $didUpdate = $inventory->updateProduct($productExternalRepresentation->toInventoryResourceId(), $productResource);

        // Push is complete
        return ["updated" => $didUpdate];
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
