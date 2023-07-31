<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.third-parties.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ScreenshotsController;
use Neo\Models\ScreenshotRequest;


Route::group([
                 "middleware" => "third-party",
                 "prefix"     => "v1/third-parties",
             ], function () {
    /*
    |--------------------------------------------------------------------------
    | Bursts
    |--------------------------------------------------------------------------
    */

    Route::model("screenshotRequest", ScreenshotRequest::class);

    Route::post("screenshots-requests/{screenshotRequest}/_receive", [ScreenshotsController::class, "receive"]);
});
