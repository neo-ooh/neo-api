<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.libraries.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ContentsController;
use Neo\Http\Controllers\CreativesController;
use Neo\Http\Controllers\LibrariesController;
use Neo\Http\Controllers\LibrariesSharesController;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Library;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {

    /*
    |----------------------------------------------------------------------
    | Libraries
    |----------------------------------------------------------------------
    */

    Route::model("library", Library::class);

    Route::   get("libraries"                   , LibrariesController::class . "@index");
    Route::   get("libraries/_query"            , LibrariesController::class . "@query");
    Route::  post("libraries"                   , LibrariesController::class . "@store");

    Route::   get("libraries/{library}"         , LibrariesController::class . "@show");
    Route::   put("libraries/{library}"         , LibrariesController::class . "@update");
    Route::delete("libraries/{library}"         , LibrariesController::class . "@destroy");

    Route::   get('libraries/{library}/contents', LibrariesController::class . "@contents");

    /*
    |----------------------------------------------------------------------
    | Libraries Shares
    |----------------------------------------------------------------------
    */

    Route::   get("libraries/{library}/shares"         , LibrariesSharesController::class . "@index");
    Route::   put("libraries/{library}/shares"         , LibrariesSharesController::class . "@store");
    Route::delete("libraries/{library}/shares"         , LibrariesSharesController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Contents
    |----------------------------------------------------------------------
    */

    Route::bind("content", fn($id) => Content::withTrashed()->find($id));

    Route::  post("contents"               , ContentsController::class . "@store");
    Route::   get("contents/{content}"     , ContentsController::class . "@show");
    Route::   put("contents/{content}"     , ContentsController::class . "@update");
    Route::   put("contents/{content}/swap", ContentsController::class . "@swap");
    Route::delete("contents/{content}"     , ContentsController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Creatives
    |----------------------------------------------------------------------
    */

    Route::model("creative", Creative::class);

    Route::  post("creatives", CreativesController::class . "@store");
    Route::delete("creatives/{creative}", CreativesController::class . "@destroy");
});
