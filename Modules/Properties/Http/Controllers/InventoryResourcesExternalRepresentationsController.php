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
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryResource;

class InventoryResourcesExternalRepresentationsController extends Controller {
    public function index(InventoryResource $inventoryResource) {

    }

    public function store(StoreExternalRepresentationRequest $request, InventoryResource $inventoryResource) {
        $externalResource               = new ExternalInventoryResource();
        $externalResource->resource_id  = $inventoryResource->getKey();
        $externalResource->inventory_id = $request->input("inventory_id");
        $externalResource->type         = $inventoryResource->type;
        $externalResource->external_id  = $request->input("external_id");
        $externalResource->context      = $request->input("context", []);
        $externalResource->save();

        new Response($externalResource, 201);
    }


    public function destroy(DestroyExternalRepresentationRequest $request, InventoryResource $inventoryResource, ExternalInventoryResource $externalRepresentation) {
        $externalRepresentation->delete();

        return new Response();
    }
}
