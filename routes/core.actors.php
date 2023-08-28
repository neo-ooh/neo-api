<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.actors.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ActorsAccessesController;
use Neo\Http\Controllers\ActorsController;
use Neo\Http\Controllers\ActorsLogosController;
use Neo\Http\Controllers\ActorsPhoneController;
use Neo\Http\Controllers\ActorsSharingsController;
use Neo\Http\Controllers\ActorsTagsController;
use Neo\Http\Controllers\TwoFactorAuthController;
use Neo\Models\Actor;
use Neo\Models\Tag;
use Neo\Modules\Properties\Http\Controllers\TagsController;

Route::group([
	             "middleware" => "default",
	             "prefix"     => "v1",
             ], function () {

	Route::model("actor", Actor::class);

	Route::   get("actors", ActorsController::class . "@index");
	Route::  get("actors/_by_id", ActorsController::class . "@byId");
	Route::  post("actors", ActorsController::class . "@store");

	Route::   get("actors/{actor}", ActorsController::class . "@show");
	Route::   put("actors/{actor}", ActorsController::class . "@update");
	Route::delete("actors/{actor}", ActorsController::class . "@destroy");

	Route::  post("actors/{actor}/re-send-signup-email", ActorsController::class . "@resendWelcomeEmail");

	Route::   get("actors/{actor}/impersonate", ActorsController::class . "@impersonate");

	Route::   get("actors/{actor}/security", ActorsController::class . "@security");

	Route::  post("actors/{actor}/two-fa/validate", TwoFactorAuthController::class . "@forceValidateToken");
	Route::  post("actors/{actor}/two-fa/recycle", TwoFactorAuthController::class . "@recycle");


	/*
	|----------------------------------------------------------------------
	| Actors Additional Accesses
	|----------------------------------------------------------------------
	*/

	Route::post("actors/{actor}/accesses", [ActorsAccessesController::class, "sync"]);


	/*
	|----------------------------------------------------------------------
	| Actors Logos
	|----------------------------------------------------------------------
	*/

	Route::post("actors/{actor}/logo", ActorsLogosController::class . "@store");
	Route::delete("actors/{actor}/logo", ActorsLogosController::class . "@destroy");


	/*
	|----------------------------------------------------------------------
	| Actors Shares
	|----------------------------------------------------------------------
	*/

	Route::   get("actors/{actor}/shares", ActorsSharingsController::class . "@index");
	Route::  post("actors/{actor}/shares", ActorsSharingsController::class . "@store");
	Route::delete("actors/{actor}/shares", ActorsSharingsController::class . "@destroy");


	/*
	|----------------------------------------------------------------------
	| Actors Phones
	|----------------------------------------------------------------------
	*/

	Route::   put("actors/{actor}/phone", ActorsPhoneController::class . "@store");
	Route::delete("actors/{actor}/phone", ActorsPhoneController::class . "@destroy");


	/*
	|----------------------------------------------------------------------
	| Actors Tags
	|----------------------------------------------------------------------
	*/

	Route::model("tag", Tag::class);

	Route::   get("tags", TagsController::class . "@index");
	Route::  post("tags", TagsController::class . "@store");
	Route::   put("tags/{tag}", TagsController::class . "@update");
	Route::delete("tags/{tag}", TagsController::class . "@destroy");

	Route::   put("actors/{actor}/tags", ActorsTagsController::class . "@sync");
});
