<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportProductJob.php
 */

namespace Neo\Modules\Properties\Jobs\Products;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

class ImportProductJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        /**
         * The inventory on which we are working
         */
        private readonly int                 $inventoryID,

        /**
         * @var int Connect property ID to which the product will be added
         */
        private readonly int                 $propertyID,

        /**
         * @var int External ID of the about-to-be-created product
         */
        private readonly InventoryResourceId $externalProductId,
    ) {
    }

    /**
     * @return Product
     * @throws InvalidInventoryAdapterException
     * @throws UnsupportedInventoryFunctionalityException
     */
    public function handle(): Product {
        // Load the inventory provider
        $inventoryProvider = InventoryProvider::findOrFail($this->inventoryID);

        /** @var InventoryAdapter $inventory */
        $inventory = InventoryAdapterFactory::make($inventoryProvider);

        if (!$inventory->hasCapability(InventoryCapability::ProductsRead)) {
            // Inventory does not support reading products, stop here.
            throw new UnsupportedInventoryFunctionalityException($this->inventoryID, InventoryCapability::ProductsRead);
        }

        // Get the product from the external inventory
        /** @var IdentifiableProduct $externalProduct */
        $externalProduct = $inventory->getProduct($this->externalProductId);

        // Insert the product with the bare minimum, we'll use a `PullProduct` job to do the rest
        $product              = new Product();
        $product->property_id = $this->propertyID;
        $product->name_en     = $externalProduct->product->name[0]->value;
        $product->name_fr     = $externalProduct->product->name[0]->value;
        $product->save();

        // Insert the external ID for this product
        $product->external_representations()->insert([
                                                         "resource_id"  => $product->inventory_resource_id,
                                                         "inventory_id" => $inventory->getInventoryID(),
                                                         "type"         => $externalProduct->resourceId->type,
                                                         "external_id"  => $externalProduct->resourceId->external_id,
                                                         "context"      => json_encode($externalProduct->resourceId->context),
                                                     ]);

        // Trigger a pull of the product to load all remaining data
        (new PullProductJob($product->inventory_resource_id, $this->inventoryID))->handle();

        return $product->refresh();
    }
}
