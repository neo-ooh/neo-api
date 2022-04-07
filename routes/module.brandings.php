<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.brandings.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\BrandingsController;
use Neo\Http\Controllers\BrandingsFilesController;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], function () {
    /*
    |----------------------------------------------------------------------
    | Brandings
    |----------------------------------------------------------------------
    */

    Route::   get("brandings", BrandingsController::class . "@index");
    Route::  post("brandings", BrandingsController::class . "@store");

    Route::   get("brandings/{branding}", BrandingsController::class . "@show");
    Route::   put("brandings/{branding}", BrandingsController::class . "@update");
    Route::delete("brandings/{branding}", BrandingsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Brandings Files
    |----------------------------------------------------------------------
    */

    Route::   get("brandings/{branding}/files", BrandingsFilesController::class . "@index");
    Route::  post("brandings/{branding}/files", BrandingsFilesController::class . "@store");
    Route::delete("brandings/{branding}/files/{file}", BrandingsFilesController::class . "@destroy");
});
