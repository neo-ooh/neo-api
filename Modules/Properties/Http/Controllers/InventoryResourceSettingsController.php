<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryResourceSettingsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\InventoryResourceSettings\ListResourceSettingsRequest;
use Neo\Modules\Properties\Http\Requests\InventoryResourceSettings\RemoveResourceSettingsRequest;
use Neo\Modules\Properties\Http\Requests\InventoryResourceSettings\ShowResourceSettingsRequest;
use Neo\Modules\Properties\Http\Requests\InventoryResourceSettings\UpdateResourceSettingsRequest;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Modules\Properties\Models\ResourceInventorySettings;

class InventoryResourceSettingsController extends Controller {
    public function index(ListResourceSettingsRequest $request, InventoryResource $inventoryResource) {
        return new Response($inventoryResource->inventories_settings);
    }

    public function show(ShowResourceSettingsRequest $request, InventoryResource $inventoryResource, ResourceInventorySettings $inventorySettings) {
        return new Response($inventorySettings);
    }

    public function update(UpdateResourceSettingsRequest $request, InventoryResource $inventoryResource, int $inventorySettingsId) {
        ResourceInventorySettings::query()->updateOrInsert([
                                                               "resource_id"  => $inventoryResource->id,
                                                               "inventory_id" => $inventorySettingsId,
                                                           ],
                                                           [
                                                               "is_enabled"   => $request->input("is_enabled"),
                                                               "push_enabled" => $request->input("push_enabled"),
                                                               "pull_enabled" => $request->input("pull_enabled"),
                                                               "settings"     => json_encode($request->input("context", [])),
                                                           ]);
    }

    public function destroy(RemoveResourceSettingsRequest $request, InventoryResource $inventoryResource, ResourceInventorySettings $inventorySettings) {

        ResourceInventorySettings::query()
                                 ->where("resource_id", "=", $inventoryResource->getKey())
                                 ->where("inventory_id", "=", $inventorySettings->inventory_id)
                                 ->delete();

        return new Response();
    }
}
