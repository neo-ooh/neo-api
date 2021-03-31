<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - api.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\AccessTokensController;
use Neo\Http\Controllers\ActorsAccessesController;
use Neo\Http\Controllers\ActorsCampaignsController;
use Neo\Http\Controllers\ActorsCapabilitiesController;
use Neo\Http\Controllers\ActorsController;
use Neo\Http\Controllers\ActorsLocationsController;
use Neo\Http\Controllers\ActorsLogosController;
use Neo\Http\Controllers\ActorsRolesController;
use Neo\Http\Controllers\ActorsSharingsController;
use Neo\Http\Controllers\BrandingsController;
use Neo\Http\Controllers\BrandingsFilesController;
use Neo\Http\Controllers\BroadSignCriteriaController;
use Neo\Http\Controllers\BroadSignSeparationsController;
use Neo\Http\Controllers\BroadSignTriggersController;
use Neo\Http\Controllers\BurstsController;
use Neo\Http\Controllers\CampaignsController;
use Neo\Http\Controllers\CapabilitiesController;
use Neo\Http\Controllers\ContainersController;
use Neo\Http\Controllers\ContentsController;
use Neo\Http\Controllers\CreativesController;
use Neo\Http\Controllers\CustomersController;
use Neo\Http\Controllers\DisplayTypesController;
use Neo\Http\Controllers\FormatsController;
use Neo\Http\Controllers\FormatsDisplayTypesController;
use Neo\Http\Controllers\FormatsLayoutsController;
use Neo\Http\Controllers\FramesController;
use Neo\Http\Controllers\HeadlinesController;
use Neo\Http\Controllers\InventoryController;
use Neo\Http\Controllers\LibrariesController;
use Neo\Http\Controllers\LocationsController;
use Neo\Http\Controllers\NetworkController;
use Neo\Http\Controllers\ParamsController;
use Neo\Http\Controllers\ReportsController;
use Neo\Http\Controllers\ReviewsController;
use Neo\Http\Controllers\ReviewsTemplatesController;
use Neo\Http\Controllers\RolesActorsController;
use Neo\Http\Controllers\RolesCapabilitiesController;
use Neo\Http\Controllers\RolesController;
use Neo\Http\Controllers\SchedulesController;
use Neo\Http\Controllers\ScreenshotsController;
use Neo\Http\Controllers\StatsController;
use Neo\Models\AccessToken;
use Neo\Models\Actor;
use Neo\Models\BroadSignCriteria;
use Neo\Models\BroadSignSeparation;
use Neo\Models\BroadSignTrigger;
use Neo\Models\Burst;
use Neo\Models\Campaign;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Format;
use Neo\Models\FormatLayout;
use Neo\Models\Frame;
use Neo\Models\Headline;
use Neo\Models\HeadlineMessage;
use Neo\Models\Library;
use Neo\Models\Location;
use Neo\Models\Param;
use Neo\Models\Report;
use Neo\Models\ReviewTemplate;
use Neo\Models\Role;
use Neo\Models\Schedule;
use Neo\Models\Screenshot;

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

Route::prefix("v1")->group(function () {
    /*
    |----------------------------------------------------------------------
    | Access Token
    |----------------------------------------------------------------------
    */

    Route::model("accessToken", AccessToken::class);

    Route::   get("access-tokens", AccessTokensController::class ."@index"                )->name("access-tokens.index");
    Route::  post("access-tokens", AccessTokensController::class ."@store"                )->name("access-tokens.store");
    Route::   get("access-tokens/{accessToken}", AccessTokensController::class ."@show"   )->name("access-tokens.show");
    Route::   put("access-tokens/{accessToken}", AccessTokensController::class ."@update" )->name("access-tokens.update");
    Route::delete("access-tokens/{accessToken}", AccessTokensController::class ."@destroy")->name("access-tokens.destroy2   ");

    /*
    |----------------------------------------------------------------------
    | Actors
    |----------------------------------------------------------------------
    */

    Route::model("actor", Actor::class);

    Route::   get("actors", ActorsController::class . "@index")->name("actors.index");
    Route::  post("actors", ActorsController::class . "@store")->name("actors.store");

    Route::   get("actors/{actor}", ActorsController::class . "@show")->name("actors.show");
    Route::   put("actors/{actor}", ActorsController::class . "@update")->name("actors.update");
    Route::delete("actors/{actor}", ActorsController::class . "@destroy")->name("actors.destroy");

    Route::post("actors/{actor}/re-send-signup-email", ActorsController::class . "@resendWelcomeEmail")
         ->name("actors.re-send-signup-email");


    /*
    |----------------------------------------------------------------------
    | Actors Accesses
    |----------------------------------------------------------------------
    */

    Route::post("actors/{actor}/accesses", ActorsAccessesController::class . "@sync")
         ->name("actors.accesses.sync");


    /*
    |----------------------------------------------------------------------
    | Actors' Campaigns
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/campaigns", ActorsCampaignsController::class . "@index")
         ->name("actors.campaigns.index");


    /*
    |----------------------------------------------------------------------
    | Actors Capabilities
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/capabilities", ActorsCapabilitiesController::class . "@index")
         ->name("actors.capabilities.index");
    Route::put("actors/{actor}/capabilities", ActorsCapabilitiesController::class . "@sync")
         ->name("actors.capabilities.sync");


    /*
    |----------------------------------------------------------------------
    | Actors Locations
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/locations", ActorsLocationsController::class . "@index")->name("actors.locations.index");
    Route::put("actors/{actor}/locations", ActorsLocationsController::class . "@sync")->name("actors.locations.sync");


    /*
    |----------------------------------------------------------------------
    | Actors Logos
    |----------------------------------------------------------------------
    */

    Route::post("actors/{actor}/logo", ActorsLogosController::class . "@store")->name("actors.logo.store");
    Route::delete("actors/{actor}/logo", ActorsLogosController::class . "@destroy")->name("actors.logo.destroy");


    /*
    |----------------------------------------------------------------------
    | Actors Roles
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/roles", ActorsRolesController::class . "@index")->name("actors.roles.index");
    Route::put("actors/{actor}/roles", ActorsRolesController::class . "@sync")->name("actors.roles.sync");


    /*
    |----------------------------------------------------------------------
    | Actors Shares
    |----------------------------------------------------------------------
    */

    Route::   get("actors/{actor}/shares", ActorsSharingsController::class . "@index")->name("actors.shares.index");
    Route::  post("actors/{actor}/shares", ActorsSharingsController::class . "@store")->name("actors.shares.store");
    Route::delete("actors/{actor}/shares", ActorsSharingsController::class . "@destroy")->name("actors.shares.destroy");


    /*
    |----------------------------------------------------------------------
    | Brandings
    |----------------------------------------------------------------------
    */

    Route::   get("brandings", BrandingsController::class . "@index")->name("brandings.index");
    Route::  post("brandings", BrandingsController::class . "@store")->name("brandings.store");

    Route::   get("brandings/{branding}", BrandingsController::class . "@show")->name("brandings.show");
    Route::   put("brandings/{branding}", BrandingsController::class . "@update")->name("brandings.update");
    Route::delete("brandings/{branding}", BrandingsController::class . "@destroy")->name("brandings.destroy");


    /*
    |----------------------------------------------------------------------
    | Brandings Files
    |----------------------------------------------------------------------
    */

    Route::   get("brandings/{branding}/files", BrandingsFilesController::class . "@index")
         ->name("brandings.files.index");
    Route::  post("brandings/{branding}/files", BrandingsFilesController::class . "@store")
         ->name("brandings.files.store");
    Route::delete("brandings/{branding}/files", BrandingsFilesController::class . "@destroy")
         ->name("brandings.files.destroy");


    /*
    |----------------------------------------------------------------------
    | Broadsign
    |----------------------------------------------------------------------
    */


    Route::prefix("broadsign")->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Criteria
        |--------------------------------------------------------------------------
        */

        Route::model("criteria", BroadSignCriteria::class);

        Route::get("criteria", BroadSignCriteriaController::class . "@index")->name('broadsign.criteria.index');
        Route::get("criteria/{criteria}", BroadSignCriteriaController::class . "@show")->name('broadsign.criteria.show');
        Route::post("criteria", BroadSignCriteriaController::class . "@store")->name('broadsign.criteria.store');
        Route::put("criteria/{criteria}", BroadSignCriteriaController::class . "@update")->name('broadsign.criteria.update');
        Route::delete("criteria/{criteria}", BroadSignCriteriaController::class . "@destroy")->name('broadsign.criteria.delete');

        /*
        |--------------------------------------------------------------------------
        | Triggers
        |--------------------------------------------------------------------------
        */

        Route::model("trigger", BroadSignTrigger::class);

        Route::get("triggers", BroadSignTriggersController::class . "@index")->name('broadsign.triggers.index');
        Route::get("triggers/{trigger}", BroadSignTriggersController::class . "@show")->name('broadsign.triggers.show');
        Route::post("triggers", BroadSignTriggersController::class . "@store")->name('broadsign.triggers.store');
        Route::put("triggers/{trigger}", BroadSignTriggersController::class . "@update")->name('broadsign.triggers.update');
        Route::delete("triggers/{trigger}", BroadSignTriggersController::class . "@destroy")->name('broadsign.triggers.delete');

        /*
        |--------------------------------------------------------------------------
        | Separations
        |--------------------------------------------------------------------------
        */

        Route::model("separation", BroadSignSeparation::class);

        Route::get("separations", BroadSignSeparationsController::class . "@index")->name('broadsign.separations.index');
        Route::get("separations/{separation}", BroadSignSeparationsController::class . "@show")
             ->name('broadsign.separations.show');
        Route::post("separations", BroadSignSeparationsController::class . "@store")->name('broadsign.separations.store');
        Route::put("separations/{separation}", BroadSignSeparationsController::class . "@update")
             ->name('broadsign.separations.update');
        Route::delete("separations/{separation}", BroadSignSeparationsController::class . "@destroy")
             ->name('broadsign.separations.delete');
    });

    /*
    |----------------------------------------------------------------------
    | Bursts
    |----------------------------------------------------------------------
    */

    Route::model("burst", Burst::class);

    Route::  post("bursts", BurstsController::class . "@store")->name("bursts.store");
    Route::   get("bursts/{burst}", BurstsController::class . "@show")->name("bursts.show");
    Route::delete("bursts/{burst}", BurstsController::class . "@destroy")->name("bursts.destroy");


    /*
    |----------------------------------------------------------------------
    | Campaigns
    |----------------------------------------------------------------------
    */

    Route::model("campaign", Campaign::class);

    Route::   get("campaigns", CampaignsController::class . "@index")->name("campaigns.index");
    Route::  post("campaigns", CampaignsController::class . "@store")->name("campaigns.store");

    Route::   get("campaigns/{campaign}", CampaignsController::class . "@show")->name("campaigns.show");
    Route::   put("campaigns/{campaign}", CampaignsController::class . "@update")->name("campaigns.update");
    Route::delete("campaigns/{campaign}", CampaignsController::class . "@destroy")->name("campaigns.destroy");

    Route::   put("campaigns/{campaign}/locations", CampaignsController::class . "@syncLocations")
         ->name("campaigns.locations.sync");

    Route::delete("campaigns/{campaign}/locations/{location}", CampaignsController::class . "@removeLocation")
         ->name("campaigns.locations.destroy");


    /*
    |----------------------------------------------------------------------
    | Capabilities
    |----------------------------------------------------------------------
    */

    Route::get("capabilities", CapabilitiesController::class . "@index")->name("capabilities.index");
    Route::put("capabilities/{capability}", CapabilitiesController::class . "@update")->name("capabilities.update");


    /*
    |----------------------------------------------------------------------
    | Containers
    |----------------------------------------------------------------------
    */

    Route::get("containers", ContainersController::class . "@index")->name("containers.index");


    /*
    |----------------------------------------------------------------------
    | Contents
    |----------------------------------------------------------------------
    */

    Route::bind("content", fn($id) => Content::withTrashed()->find($id));

    Route::  post("contents", ContentsController::class . "@store")->name("contents.store");
    Route::   get("contents/{content}", ContentsController::class . "@show")->name("contents.show");
    Route::   put("contents/{content}", ContentsController::class . "@update")->name("contents.update");
    Route::   put("contents/{content}/swap", ContentsController::class . "@swap")->name("contents.swap");
    Route::delete("contents/{content}", ContentsController::class . "@destroy")->name("contents.destroy");


    /*
    |----------------------------------------------------------------------
    | Creatives
    |----------------------------------------------------------------------
    */

    Route::model("creative", Creative::class);

    Route::  post("contents/{content}", CreativesController::class . "@store")->name("creatives.store");
    Route::delete("creatives/{creative}", CreativesController::class . "@destroy")->name("creatives.destroy");


    /*
    |----------------------------------------------------------------------
    | Customers
    |----------------------------------------------------------------------
    */

    Route::get("customers", CustomersController::class . "@index")->name("customers.index");
    Route::get("customers/{customer}", CustomersController::class . "@show")->name("customers.show");


    /*
    |----------------------------------------------------------------------
    | Display Types
    |----------------------------------------------------------------------
    */

    Route::get("display-types", DisplayTypesController::class . "@index")->name("display-types.index");


    /*
    |----------------------------------------------------------------------
    | Formats
    |----------------------------------------------------------------------
    */

    Route::model("format", Format::class);

    Route::get("formats", FormatsController::class . "@index")->name("formats.index");
    Route::get("formats/_query", FormatsController::class . "@query")->name("formats.query");
    Route::post("formats", FormatsController::class . "@store")->name("formats.store");
    Route::get("formats/{format}", FormatsController::class . "@show")->name("formats.show");
    Route::put("formats/{format}", FormatsController::class . "@update")->name("formats.update");


    /*
    |----------------------------------------------------------------------
    | Formats Display Types
    |----------------------------------------------------------------------
    */

    Route::   put("formats/{format}/display-types", FormatsDisplayTypesController::class . "@sync")
         ->name("formats.display-types.sync");


    /*
    |----------------------------------------------------------------------
    | Formats Layouts
    |----------------------------------------------------------------------
    */

    Route::model("layout", FormatLayout::class);

    Route::  post("layouts", FormatsLayoutsController::class . "@store")->name("layouts.store");
    Route::   put("layouts/{layout}", FormatsLayoutsController::class . "@update")->name("layouts.update");
    Route::delete("layouts/{layout}", FormatsLayoutsController::class . "@destroy")->name("layouts.destroy");


    /*
    |----------------------------------------------------------------------
    | Frames
    |----------------------------------------------------------------------
    */

    Route::model("frame", Frame::class);

    Route::  post("frames", FramesController::class . "@store")->name("frames.index");
    Route::   put("frames/{frame}", FramesController::class . "@update")->name("frames.update");
    Route::delete("frames/{frame}", FramesController::class . "@destroy")->name("frames.destroy");


    /*
    |----------------------------------------------------------------------
    | Headlines
    |----------------------------------------------------------------------
    */

    Route::model("headline", Headline::class);
    Route::model("headlineMessage", HeadlineMessage::class);

    Route::   get("headlines"           , HeadlinesController::class . "@index"  )->name("headlines.index");
    Route::   get("headlines/_current"  , HeadlinesController::class . "@current")->name("headlines.current");
    Route::   get("headlines/{headline}", HeadlinesController::class . "@show"   )->name("headlines.show");
    Route::  post("headlines"           , HeadlinesController::class . "@store"  )->name("headlines.store");
    Route::   put("headlines/{headline}", HeadlinesController::class . "@update" )->name("headlines.update");
    Route::delete("headlines/{headline}", HeadlinesController::class . "@delete" )->name("headlines.destroy");
    Route::put("headlines/{headline}/messages/{headlineMessage}", HeadlinesController::class . "@updateMessage" )->name("headlines.messages.update");


    /*
    |----------------------------------------------------------------------
    | Inventory
    |----------------------------------------------------------------------
    */


    Route::get("inventory", InventoryController::class . "@index")->name("inventory.index");


    /*
    |----------------------------------------------------------------------
    | Libraries
    |----------------------------------------------------------------------
    */

    Route::model("library", Library::class);

    Route::   get("libraries", LibrariesController::class . "@index")->name("libraries.index");
    Route::  post("libraries", LibrariesController::class . "@store")->name("libraries.store");

    Route::   get("libraries/{library}", LibrariesController::class . "@show")->name("libraries.show");
    Route::   put("libraries/{library}", LibrariesController::class . "@update")->name("libraries.update");
    Route::delete("libraries/{library}", LibrariesController::class . "@destroy")->name("libraries.destroy");

    Route::   get('libraries/{library}/contents', LibrariesController::class . "@contents")->name("libraries.content");


    /*
    |----------------------------------------------------------------------
    | Locations
    |----------------------------------------------------------------------
    */

    Route::model("location", Location::class);

    Route::get("locations"           , LocationsController::class . "@index"       )->name("locations.index");
    Route::get("locations/_search"   , LocationsController::class . "@search"      )->name("locations.search");
    Route::get("locations/_network"  , LocationsController::class . "@allByNetwork")->name("locations.network");
    Route::get("locations/{location}", LocationsController::class . "@show"        )->name("locations.show");
    Route::put("locations/{location}", LocationsController::class . "@update"      )->name("locations.update");


    /*
    |----------------------------------------------------------------------
    | Network
    |----------------------------------------------------------------------
    */

    Route:: get("network/refresh", NetworkController::class . "@refresh")->name("network.refresh");

    /*
    |----------------------------------------------------------------------
    | Parameters
    |----------------------------------------------------------------------
    */

    Route::model("parameter", Param::class);

    Route::  get("params/{parameter:slug}", ParamsController::class . "@show")->name("params.show");
    Route::  post("params/{parameter:slug}", ParamsController::class . "@update")->name("params.update");
    Route::  put("params/{parameter:slug}", ParamsController::class . "@update")->name("params.update-put");


    /*
    |----------------------------------------------------------------------
    | Reports
    |----------------------------------------------------------------------
    */

    Route::model("report", Report::class);

    Route::  post("reports", ReportsController::class . "@store")->name("reports.store");
    Route::   get("reports/{report}", ReportsController::class . "@show")->name("reports.show");
    Route::   get("reports/{report}/refresh", ReportsController::class . "@refresh")->name("reports.refresh");
    Route::delete("reports/{report}", ReportsController::class . "@destroy")->name("reports.destroy");


    /*
    |----------------------------------------------------------------------
    | Reviews
    |----------------------------------------------------------------------
    */

    Route::post("schedules/{schedule}/reviews", ReviewsController::class . "@store")->name("reviews.store");


    /*
    |----------------------------------------------------------------------
    | Reviews Templates
    |----------------------------------------------------------------------
    */

    Route::model("template", ReviewTemplate::class);

    Route::   get("review-templates", ReviewsTemplatesController::class . "@index")->name("reviews.templates.index");
    Route::  post("review-templates", ReviewsTemplatesController::class . "@store")->name("reviews.templates.store");
    Route::   put("review-templates/{template}", ReviewsTemplatesController::class . "@update")
         ->name("reviews.templates.update");
    Route::delete("review-templates/{template}", ReviewsTemplatesController::class . "@destroy")
         ->name("reviews.templates.destroy");


    /*
    |----------------------------------------------------------------------
    | Roles
    |----------------------------------------------------------------------
    */

    Route::model("role", Role::class);

    Route::   get("roles", RolesController::class . "@index")->name("roles.index");
    Route::  post("roles", RolesController::class . "@store")->name("roles.store");

    Route::   get("roles/{role}", RolesController::class . "@show")->name("roles.show");
    Route::   put("roles/{role}", RolesController::class . "@update")->name("roles.update");
    Route::delete("roles/{role}", RolesController::class . "@destroy")->name("roles.destroy");


    /*
    |----------------------------------------------------------------------
    | Roles Capabilities
    |----------------------------------------------------------------------
    */

    Route::   get("roles/{role}/capabilities", RolesCapabilitiesController::class . "@index")
         ->name("roles.capabilities.index");
    Route::  post("roles/{role}/capabilities", RolesCapabilitiesController::class . "@store")
         ->name("roles.capabilities.store");
    Route::   put("roles/{role}/capabilities", RolesCapabilitiesController::class . "@update")
         ->name("roles.capabilities.update");
    Route::delete("roles/{role}/capabilities", RolesCapabilitiesController::class . "@destroy")
         ->name("roles.capabilities.destroy");


    /*
    |----------------------------------------------------------------------
    | Roles Actors
    |----------------------------------------------------------------------
    */

    Route::   get("roles/{role}/actors", RolesActorsController::class . "@index")->name("roles.actors.index");
    Route::  post("roles/{role}/actors", RolesActorsController::class . "@store")->name("roles.actors.store");
    Route::delete("roles/{role}/actors", RolesActorsController::class . "@destroy")->name("roles.actors.destroy");


    /*
    |----------------------------------------------------------------------
    | Schedules
    |----------------------------------------------------------------------
    */

    Route::model("schedule", Schedule::class);

    Route::   get("schedules/pending", SchedulesController::class . "@pending")->name("schedules.pending");
    Route::   put("schedules/{schedule}", SchedulesController::class . "@update")->name("schedules.update");
    Route::delete("schedules/{schedule}", SchedulesController::class . "@destroy")->name("schedules.destroy");

    Route::  post("campaigns/{campaign}/reorder", SchedulesController::class . "@reorder")->name("schedules.reorder");
    Route::  post("campaigns/{campaign}/schedules", SchedulesController::class . "@store")->name("schedules.store");
    Route::  post("campaigns/{campaign}/insert", SchedulesController::class . "@insert")->name("schedules.insert");


    /*
    |----------------------------------------------------------------------
    | Screenshots
    |----------------------------------------------------------------------
    */

    Route::model("screenshot", Screenshot::class);

    Route::delete("screenshots/{screenshot}", ScreenshotsController::class . "@destroy")->name("screenshots.destroy");


    /*
    |----------------------------------------------------------------------
    | Screenshots
    |----------------------------------------------------------------------
    */

    Route::get("stats", StatsController::class . "@index")->name("stats.index");
});
