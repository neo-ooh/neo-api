<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - formats.php
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
use Neo\Modules\Broadcast\Http\Controllers\FormatCropFramesController;
use Neo\Modules\Broadcast\Http\Controllers\FormatsController;
use Neo\Modules\Broadcast\Http\Controllers\FormatsDisplayTypesController;
use Neo\Modules\Broadcast\Http\Controllers\FormatsLayoutsController;
use Neo\Modules\Broadcast\Http\Controllers\FormatsLoopConfigurationsController;
use Neo\Modules\Broadcast\Http\Controllers\FramesController;
use Neo\Modules\Broadcast\Http\Controllers\LayoutsController;
use Neo\Modules\Broadcast\Http\Controllers\LoopConfigurationsController;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\FormatCropFrame;
use Neo\Modules\Broadcast\Models\Frame;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Modules\Broadcast\Models\LoopConfiguration;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v2",
             ], static function () {
    /*
    |----------------------------------------------------------------------
    | Layouts
    |----------------------------------------------------------------------
    */

    Route::model("layout", Layout::class);

    Route::   get("layouts", LayoutsController::class . "@index");
    Route::  post("layouts", LayoutsController::class . "@store");
    Route::   get("layouts/{layout}", LayoutsController::class . "@show");
    Route::   put("layouts/{layout}", LayoutsController::class . "@update");
    Route::delete("layouts/{layout}", LayoutsController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Frames
    |----------------------------------------------------------------------
    */

    Route::model("frame", Frame::class);

    Route::   get("layouts/{layout}/frames", FramesController::class . "@index");
    Route::  post("layouts/{layout}/frames", FramesController::class . "@store");
    Route::   put("layouts/{layout}/frames/{frame}", FramesController::class . "@update");
    Route::delete("layouts/{layout}/frames/{frame}", FramesController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Formats
    |----------------------------------------------------------------------
    */

    Route::model("format", Format::class);

    Route::   get("formats", FormatsController::class . "@index");
    Route::   get("formats/_by_id", FormatsController::class . "@byIds");
    Route::  post("formats", FormatsController::class . "@store");
    Route::   get("formats/{format}", FormatsController::class . "@show");
    Route::   put("formats/{format}", FormatsController::class . "@update");
    Route::  post("formats/{format}/_clone", FormatsController::class . "@clone");
    Route::delete("formats/{format}", FormatsController::class . "@destroy");

    Route::put("formats/{format}/layouts/_sync", FormatsLayoutsController::class . "@sync");
    Route::put("formats/{format}/display-types/_sync", FormatsDisplayTypesController::class . "@sync");
    Route::put("formats/{format}/loop-configurations/_sync", FormatsLoopConfigurationsController::class . "@sync");

    /*
    |----------------------------------------------------------------------
    | Format Crop Frames
    |----------------------------------------------------------------------
    */

    Route::model("formatCropFrame", FormatCropFrame::class);

    Route::   get("formats/{format}/crop-frames", [FormatCropFramesController::class, "index"]);
    Route::  post("formats/{format}/crop-frames", [FormatCropFramesController::class, "store"]);
    Route::   get("formats/{format}/crop-frames/{formatCropFrame}", [FormatCropFramesController::class, "show"]);
    Route::   put("formats/{format}/crop-frames/{formatCropFrame}", [FormatCropFramesController::class, "update"]);
    Route::delete("formats/{format}/crop-frames/{formatCropFrame}", [FormatCropFramesController::class, "destroy"]);

    /*
    |----------------------------------------------------------------------
    | Loop Configuration
    |----------------------------------------------------------------------
    */

    Route::model("loopConfiguration", LoopConfiguration::class);

    Route::   get("loop-configurations", LoopConfigurationsController::class . "@index");
    Route::  post("loop-configurations", LoopConfigurationsController::class . "@store");
    Route::   get("loop-configurations/{loopConfiguration}", LoopConfigurationsController::class . "@show");
    Route::   put("loop-configurations/{loopConfiguration}", LoopConfigurationsController::class . "@update");
    Route::delete("loop-configurations/{loopConfiguration}", LoopConfigurationsController::class . "@destroy");
});
