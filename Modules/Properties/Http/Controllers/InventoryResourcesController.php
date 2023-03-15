<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourcesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\InventoryResources\ShowInventoryResourcesRequest;
use Neo\Modules\Properties\Models\InventoryResource;

class InventoryResourcesController extends Controller {
    public function show(ShowInventoryResourcesRequest $request, InventoryResource $inventoryResource) {
        return new Response($inventoryResource->loadPublicRelations());
    }
}
