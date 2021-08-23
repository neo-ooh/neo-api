<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.inventory.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\InventoryController;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {

    Route::get("inventory", InventoryController::class . "@index");
});
