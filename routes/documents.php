<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - documents.php
 */

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\DocumentsGenerationController;
use Neo\Http\Controllers\TrafficController;

Route::prefix("documents")->group(function () {
    Route::get("traffic/_export", TrafficController::class . "@export")
         ->name("traffic.export");
    Route::post("{document}", DocumentsGenerationController::class . "@make")
         ->name("documents.make");
});
