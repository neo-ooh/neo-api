<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryProvidersExternalResourcesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\InventoryProvidersProperties\ListExternalResourcesRequest;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Resources\InventoryExternalResource;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\PropertyResource;

class InventoryProvidersExternalResourcesController extends Controller {
    public function index(ListExternalResourcesRequest $request, InventoryProvider $inventoryProvider) {
        // Get an instance of the inventory
        /** @var InventoryAdapter $inventory */
        $inventory = InventoryAdapterFactory::make($inventoryProvider);

        $resources = collect();
        $type      = "";

        switch ($request->input("type")) {
            case "property":
                $type = "property";

                // Does this inventory supports properties ?
                if (!$inventory->hasCapability(InventoryCapability::PropertiesRead)) {
                    return new Response([]);
                }

                $resources = Collection::make($inventory->listProperties())->map(
                    fn(PropertyResource $resource) => new InventoryExternalResource(
                        type       : "property",
                        name       : $resource->property_name,
                        external_id: $resource->property_id,
                    )
                );
                break;
            case "product":
                $type = "product";

                // Does this inventory supports properties ?
                if ($inventory->hasCapability(InventoryCapability::PropertiesRead)) {
                    // Get the property id for this inventory
                    /** @var Property $property */
                    $property = Property::query()
                                        ->where("inventory_resource_id", "=", $request->input("property_id"))
                                        ->firstOrFail();
                    /** @var ExternalInventoryResource|null $representation */
                    $representation = $property->external_representations()
                                               ->where("inventory_id", "=", $inventory->getInventoryID())
                                               ->withoutTrashed()
                                               ->first();

                    if (!$representation) {
                        return new Response([]);
                    }

                    $resources = Collection::make($inventory->listPropertyProducts($representation->toInventoryResourceId())
                                                            ->map(
                                                                fn(IdentifiableProduct $resource) => new InventoryExternalResource(
                                                                    type       : "property",
                                                                    name       : $resource->product->name[0]->value,
                                                                    external_id: $resource->resourceId,
                                                                )
                                                            ));
                } else {
                    $resources = Collection::make($inventory->listProducts())->map(
                        fn(IdentifiableProduct $resource) => new InventoryExternalResource(
                            type       : "product",
                            name       : $resource->product->name[0]->value,
                            external_id: $resource->resourceId,
                        )
                    );
                }
        }

        if ($request->input("only_not_associated")) {
            // We have to filter the results to only return ones whose external id is not currently in the db
            $externalIds = ExternalInventoryResource::query()
                                                    ->where("inventory_id", "=", $inventoryProvider->getKey())
                                                    ->where("type", "=", $type)
                                                    ->pluck("external_id");

            $resources = $resources->filter(fn(InventoryExternalResource $resource) => $externalIds->doesntContain(null, $resource->external_id->external_id))
                                   ->values();
        }


        return new Response($resources);
    }
}
