<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - libraries.php
 */


use Neo\Modules\Broadcast\Http\Controllers\ContentsController;
use Neo\Modules\Broadcast\Http\Controllers\CreativesController;
use Neo\Modules\Broadcast\Http\Controllers\LibrariesContentsController;
use Neo\Modules\Broadcast\Http\Controllers\LibrariesController;
use Neo\Modules\Broadcast\Http\Controllers\LibrariesSharesController;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\Library;

Route::group([
    "middleware" => "default",
    "prefix"     => "v2",
], static function () {

    /*
    |----------------------------------------------------------------------
    | Libraries
    |----------------------------------------------------------------------
    */

    Route::model("library", Library::class);

    Route::   get("libraries", LibrariesController::class . "@index");
    Route::   get("libraries/_query", LibrariesController::class . "@query");
    Route::  post("libraries", LibrariesController::class . "@store");

    Route::   get("libraries/{library}", LibrariesController::class . "@show");
    Route::   put("libraries/{library}", LibrariesController::class . "@update");
    Route::delete("libraries/{library}", LibrariesController::class . "@destroy");

    Route::   get('libraries/{library}/contents', LibrariesContentsController::class . "@index");
    Route::   put('libraries/{library}/_move', LibrariesContentsController::class . "@move");

    /*
    |----------------------------------------------------------------------
    | Libraries Shares
    |----------------------------------------------------------------------
    */

    Route::   get("libraries/{library}/shares", LibrariesSharesController::class . "@index");
    Route::  post("libraries/{library}/shares", LibrariesSharesController::class . "@store");
    Route::delete("libraries/{library}/shares", LibrariesSharesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Contents
    |----------------------------------------------------------------------
    */

    Route::bind("content", fn($id) => Content::withTrashed()->find($id));

    Route::   get("/contents/_by_id", ContentsController::class . "@byIds");
    Route::  post("/contents", ContentsController::class . "@store");

    Route::   get("/contents/{content}", ContentsController::class . "@show");
    Route::   put("/contents/{content}", ContentsController::class . "@update");
    Route::delete("/contents/{content}", ContentsController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Creatives
    |----------------------------------------------------------------------
    */

    Route::model("creative", Creative::class);

    Route::  post("contents/{content}/creatives", CreativesController::class . "@store");
    Route::delete("contents/{content}/creatives/{creative}", CreativesController::class . "@destroy");
});
