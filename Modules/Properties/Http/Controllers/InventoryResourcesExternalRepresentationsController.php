<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourcesExternalRepresentationsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\InventoryResourcesExternalRepresentations\DestroyExternalRepresentationRequest;
use Neo\Modules\Properties\Http\Requests\InventoryResourcesExternalRepresentations\StoreExternalRepresentationRequest;
use Neo\Modules\Properties\Http\Requests\InventoryResourcesExternalRepresentations\UpdateExternalRepresentationRequest;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryResource;

class InventoryResourcesExternalRepresentationsController extends Controller {
    public function store(StoreExternalRepresentationRequest $request, InventoryResource $inventoryResource): Response {
        $externalRepresentation               = new ExternalInventoryResource();
        $externalRepresentation->resource_id  = $inventoryResource->getKey();
        $externalRepresentation->inventory_id = $request->input("inventory_id");
        $externalRepresentation->type         = $inventoryResource->type;
        $externalRepresentation->external_id  = $request->input("external_id");
        $externalRepresentation->context      = $request->input("context", []);
        $externalRepresentation->save();

        return new Response($externalRepresentation, 201);
    }

    public function update(UpdateExternalRepresentationRequest $request, InventoryResource $inventoryResource, ExternalInventoryResource $externalRepresentation): Response {
        $externalRepresentation->external_id = $request->input("external_id");
        $externalRepresentation->context     = $request->input("context", []);
        $externalRepresentation->save();

        return new Response($externalRepresentation, 200);
    }


    public function destroy(DestroyExternalRepresentationRequest $request, InventoryResource $inventoryResource, ExternalInventoryResource $externalRepresentation): Response {
        $externalRepresentation->delete();

        return new Response();
    }
}
