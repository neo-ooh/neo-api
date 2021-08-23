<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.roles.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ActorsCapabilitiesController;
use Neo\Http\Controllers\ActorsRolesController;
use Neo\Http\Controllers\CapabilitiesController;
use Neo\Http\Controllers\RolesActorsController;
use Neo\Http\Controllers\RolesCapabilitiesController;
use Neo\Http\Controllers\RolesController;
use Neo\Models\Role;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {

    /*
    |----------------------------------------------------------------------
    | Capabilities
    |----------------------------------------------------------------------
    */

    Route::get("capabilities", CapabilitiesController::class . "@index");
    Route::put("capabilities/{capability}", CapabilitiesController::class . "@update");

    /*
    |----------------------------------------------------------------------
    | Roles
    |----------------------------------------------------------------------
    */

    Route::model("role", Role::class);

    Route::   get("roles", RolesController::class . "@index");
    Route::  post("roles", RolesController::class . "@store");

    Route::   get("roles/{role}", RolesController::class . "@show");
    Route::   put("roles/{role}", RolesController::class . "@update");
    Route::delete("roles/{role}", RolesController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Roles Capabilities
    |----------------------------------------------------------------------
    */

    Route::   get("roles/{role}/capabilities", RolesCapabilitiesController::class . "@index");
    Route::  post("roles/{role}/capabilities", RolesCapabilitiesController::class . "@store");
    Route::   put("roles/{role}/capabilities", RolesCapabilitiesController::class . "@update");
    Route::delete("roles/{role}/capabilities", RolesCapabilitiesController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Roles Actors
    |----------------------------------------------------------------------
    */

    Route::   get("roles/{role}/actors", RolesActorsController::class . "@index");
    Route::  post("roles/{role}/actors", RolesActorsController::class . "@store");
    Route::delete("roles/{role}/actors", RolesActorsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Actors Roles
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/roles", ActorsRolesController::class . "@index");
    Route::put("actors/{actor}/roles", ActorsRolesController::class . "@sync");

    /*
    |----------------------------------------------------------------------
    | Actors Capabilities
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/capabilities", ActorsCapabilitiesController::class . "@index");
    Route::put("actors/{actor}/capabilities", ActorsCapabilitiesController::class . "@sync");
});
