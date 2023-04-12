<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - inventories.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\InventoryProvidersController;
use Neo\Modules\Properties\Http\Controllers\InventoryProvidersExternalResourcesController;
use Neo\Modules\Properties\Http\Controllers\InventoryResourcesActionsController;
use Neo\Modules\Properties\Http\Controllers\InventoryResourcesController;
use Neo\Modules\Properties\Http\Controllers\InventoryResourceSettingsController;
use Neo\Modules\Properties\Http\Controllers\InventoryResourcesExternalRepresentationsController;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\InventoryResource;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ],
    static function () {
        /*
        |----------------------------------------------------------------------
        | Inventory Provider
        |----------------------------------------------------------------------
        */

        Route::model("inventoryProvider", InventoryProvider::class);

        Route::   get("inventories", [InventoryProvidersController::class, "index"]);
        Route::  post("inventories", [InventoryProvidersController::class, "store"]);
        Route::   get("inventories/{inventoryProvider}", [InventoryProvidersController::class, "show"]);
        Route::   put("inventories/{inventoryProvider}", [InventoryProvidersController::class, "update"]);
        Route::   put("inventories/{inventoryProvider}/_clear_cache", [InventoryProvidersController::class, "clearCache"]);
        Route::delete("inventories/{inventoryProvider}", [InventoryProvidersController::class, "destroy"]);

        Route::   get("inventories/{inventoryProvider}/external-resources", [InventoryProvidersExternalResourcesController::class, "index"]);

        /*
        |----------------------------------------------------------------------
        | Inventory Resources Settings
        |----------------------------------------------------------------------
        */

        Route::model("inventoryResource", InventoryResource::class);

        Route::   get("inventories-resources/{inventoryResource}", [InventoryResourcesController::class, "show"]);

        Route::   get("inventories-resources/{inventoryResource}/settings", [InventoryResourceSettingsController::class, "index"]);
        Route::   get("inventories-resources/{inventoryResource}/settings/{inventorySettings:inventory_id}", [InventoryResourceSettingsController::class, "show"]);
        Route::   put("inventories-resources/{inventoryResource}/settings/{inventorySettingsId}", [InventoryResourceSettingsController::class, "update"]);
        Route::delete("inventories-resources/{inventoryResource}/settings/{inventorySettings:inventory_id}", [InventoryResourceSettingsController::class, "destroy"]);

        /*
        |----------------------------------------------------------------------
        | Inventory Resources Actions
        |----------------------------------------------------------------------
        */

        Route::  post("inventories-resources/{inventoryResource}/_push", [InventoryResourcesActionsController::class, "push"]);
        Route::  post("inventories-resources/{inventoryResource}/_pull", [InventoryResourcesActionsController::class, "pull"]);
        Route::  post("inventories-resources/{inventoryResource}/_create", [InventoryResourcesActionsController::class, "create"]);
        Route::  post("inventories-resources/{inventoryResource}/_import_product", [InventoryResourcesActionsController::class, "importProduct"]);
        Route::  post("inventories-resources/{inventoryResource}/_delete", [InventoryResourcesActionsController::class, "destroy"]);

        /*
        |----------------------------------------------------------------------
        | Inventory Resources Representations
        |----------------------------------------------------------------------
        */
        Route::  post("inventories-resources/{inventoryResource}/external-representations", [InventoryResourcesExternalRepresentationsController::class, "store"]);
        Route::   put("inventories-resources/{inventoryResource}/external-representations/{externalRepresentation:id}", [InventoryResourcesExternalRepresentationsController::class, "update"]);
        Route::delete("inventories-resources/{inventoryResource}/external-representations/{externalRepresentation:id}", [InventoryResourcesExternalRepresentationsController::class, "destroy"]);
    });
