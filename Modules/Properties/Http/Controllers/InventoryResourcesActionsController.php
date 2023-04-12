<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourcesActionsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Http\Requests\InventoryActions\CreateProductRequest;
use Neo\Modules\Properties\Http\Requests\InventoryActions\DestroyExternalResourceRequest;
use Neo\Modules\Properties\Http\Requests\InventoryActions\ImportProductRequest;
use Neo\Modules\Properties\Http\Requests\InventoryActions\PullInventoryResourceRequest;
use Neo\Modules\Properties\Http\Requests\InventoryActions\PushInventoryResourceRequest;
use Neo\Modules\Properties\Jobs\Products\CreateProductJob;
use Neo\Modules\Properties\Jobs\Products\DestroyProductJob;
use Neo\Modules\Properties\Jobs\Products\ImportProductJob;
use Neo\Modules\Properties\Jobs\Products\PullProductJob;
use Neo\Modules\Properties\Jobs\Products\PushProductJob;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

class InventoryResourcesActionsController extends Controller {
    public function push(PushInventoryResourceRequest $request, InventoryResource $inventoryResource) {
        // Products can be pushed directly, but properties cannot. A push on a property, is a push on all its products
        /** @var Collection<Product> $products */
        $products = $inventoryResource->products;

        if ($inventoryResource->type === InventoryResourceType::Product && $products->isEmpty()) {
            return new Response([], 404);
        }

        // Loop on each resource
        /** @var Product $product */
        foreach ($products as $product) {
            $inventoriesID = $request->has("inventory_id")
                ? [$request->input("inventory_id")]
                : $product->enabled_inventories;

            // Trigger a loop for each enabled inventory
            foreach ($inventoriesID as $inventoryID) {
                (new PushProductJob($product->inventory_resource_id, $inventoryID))->handle();
            }
        }

        return new Response();

    }

    public function pull(PullInventoryResourceRequest $request, InventoryResource $inventoryResource) {
        // Products can be pulled directly, but properties cannot. A pull on a property, is a pull on all its products
        /** @var Collection<Product> $products */
        $products = $inventoryResource->products;

        if ($inventoryResource->type === InventoryResourceType::Product && $products->isEmpty()) {
            return new Response([], 404);
        }

        // Loop   on each resource
        /** @var Product $product */
        foreach ($products as $product) {
            $inventoriesID = $request->has("inventory_id")
                ? [$request->input("inventory_id")]
                : $product->enabled_inventories;

            // Trigger a loop for each enabled inventory
            foreach ($inventoriesID as $inventoryID) {
                $pullJob = new PullProductJob($product->inventory_resource_id, $inventoryID);
                $pullJob->handle();
            }
        }

        return new Response();
    }

    public function create(CreateProductRequest $request, InventoryResource $inventoryResource) {
        // Only produtcs can be created, make sure we're not trying to do something stupid
        if ($inventoryResource->type !== InventoryResourceType::Product) {
            return new Response([]);
        }

        $createJob = new CreateProductJob($inventoryResource->getKey(), $request->input("inventory_id"), $request->input("context", []));
        $createJob->handle();

        return new Response(["status" => "ok"]);
    }

    /**
     * @param ImportProductRequest $request
     * @param InventoryResource    $inventoryResource
     * @return Response
     * @throws InvalidInventoryAdapterException
     * @throws UnsupportedInventoryFunctionalityException
     */
    public function importProduct(ImportProductRequest $request, InventoryResource $inventoryResource) {
        $externalID = new InventoryResourceId(
            inventory_id: $request->input("inventory_id"),
            external_id : $request->input("external_id"),
            type        : InventoryResourceType::Product,
            context     : $request->input("context", [])
        );

        // Load the property to get its id
        $property = Property::query()->where("inventory_resource_id", "=", $inventoryResource->getKey())->firstOrFail();

        // Import the product synchronously
        $importJob = new ImportProductJob($request->input("inventory_id"), $property->getKey(), $externalID);
        $importJob->handle();

        return new Response($importJob->getResult());
    }

    public function destroy(DestroyExternalResourceRequest $request, InventoryResource $inventoryResource) {
        if ($inventoryResource->type !== InventoryResourceType::Product) {
            // We don't delete products
            return new Response([]);
        }

        $deleteJob = new DestroyProductJob($inventoryResource->getKey(), $request->input("inventory_id"));
        $deleteJob->handle();

        return new Response([]);
    }
}
