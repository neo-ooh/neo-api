<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.formats.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\FormatsController;
use Neo\Http\Controllers\FormatsDisplayTypesController;
use Neo\Http\Controllers\FormatsLayoutsController;
use Neo\Http\Controllers\FramesController;
use Neo\Models\Format;
use Neo\Models\FormatLayout;
use Neo\Models\Frame;
Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {

    /*
    |----------------------------------------------------------------------
    | Formats
    |----------------------------------------------------------------------
    */

    Route::model("format", Format::class);

    Route:: get("formats"         , FormatsController::class . "@index" );
    Route:: get("formats/_query"  , FormatsController::class . "@query" );
    Route::post("formats"         , FormatsController::class . "@store" );
    Route:: get("formats/{format}", FormatsController::class . "@show"  );
    Route:: put("formats/{format}", FormatsController::class . "@update");


    /*
    |----------------------------------------------------------------------
    | Formats Display Types
    |----------------------------------------------------------------------
    */

    Route::put("formats/{format}/display-types", FormatsDisplayTypesController::class . "@sync");


    /*
    |----------------------------------------------------------------------
    | Formats Layouts
    |----------------------------------------------------------------------
    */

    Route::model("layout", FormatLayout::class);

    Route::  post("layouts"         , FormatsLayoutsController::class . "@store");
    Route::   put("layouts/{layout}", FormatsLayoutsController::class . "@update");
    Route::delete("layouts/{layout}", FormatsLayoutsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Frames
    |----------------------------------------------------------------------
    */

    Route::model("frame", Frame::class);

    Route::  post("frames", FramesController::class . "@store");
    Route::   put("frames/{frame}", FramesController::class . "@update");
    Route::delete("frames/{frame}", FramesController::class . "@destroy");
});
