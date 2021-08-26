<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.documents.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\DocumentsGenerationController;

Route::group([
    "middleware" => "default",
    "prefix" => "documents"
], function () {
    Route::post("{document}", DocumentsGenerationController::class . "@make");
});
