<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryProvidersController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\ListInventoriesRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\ShowInventoryRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\StoreInventoryRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\UpdateInventoryRequest;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\StructuredColumns\InventoryProviderSettings;
use Neo\Modules\Properties\Services\InventoryType;

class InventoryProvidersController extends Controller {
    public function index(ListInventoriesRequest $request) {
        return new Response(InventoryProvider::all());
    }

    public function store(StoreInventoryRequest $request) {
        $provider           = new InventoryProvider();
        $provider->name     = $request->input("name");
        $provider->provider = $request->input("provider");

        $provider->settings          = new InventoryProviderSettings();
        $provider->settings->api_url = $request->input("api_url");
        $provider->settings->api_key = $request->input("api_key");

        switch ($provider->provider) {
            case InventoryType::Odoo:
                $provider->settings->api_username = $request->input("api_username");
                $provider->settings->database     = $request->input("database");
                break;
            case InventoryType::Hivestack:
            case InventoryType::Vistar:
            case InventoryType::Atedra:
            case InventoryType::Reach:
        }

        $provider->save();

        return new Response($provider, 201);
    }

    public function show(ShowInventoryRequest $request, InventoryProvider $inventoryProvider) {
        return new Response($inventoryProvider->loadPublicRelations());
    }

    public function update(UpdateInventoryRequest $request, InventoryProvider $inventoryProvider) {
        $inventoryProvider->name      = $request->input("name");
        $inventoryProvider->is_active = $request->input("is_active");

        $inventoryProvider->settings->api_url = $request->input("api_url");
        $inventoryProvider->settings->api_key = $request->input("api_key");

        switch ($inventoryProvider->provider) {
            case InventoryType::Odoo:
                $inventoryProvider->settings->api_username = $request->input("api_username");
                $inventoryProvider->settings->database     = $request->input("database");
                break;
            case InventoryType::Hivestack:
            case InventoryType::Vistar:
            case InventoryType::Atedra:
            case InventoryType::Reach:
        }

        $inventoryProvider->save();

        return new Response($inventoryProvider->loadPublicRelations(), 201);
    }

    public function destroy(InventoryProvider $inventoryProvider) {
        $inventoryProvider->delete();
    }
}
