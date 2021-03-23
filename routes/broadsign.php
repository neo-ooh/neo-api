<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - broadsign.php
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
use Neo\Http\Controllers\BurstsController;
use Neo\Models\Burst;

Route::prefix("v1/broadsign")->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Bursts
    |--------------------------------------------------------------------------
    */

    Route::model("burst", Burst::class);

    Route::any("burst_callback/{burst}", BurstsController::class . "@receive")->name('broadsign.bursts.receive');
});
