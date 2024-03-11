<?php

use Illuminate\Support\Facades\Route;
use Neo\Modules\Demographics\Http\Controllers\AreasController;
use Neo\Modules\Demographics\Http\Controllers\AreaTypesController;
use Neo\Modules\Demographics\Http\Controllers\DatasetsController;
use Neo\Modules\Demographics\Http\Controllers\DatasetsDatapointsController;
use Neo\Modules\Demographics\Http\Controllers\DatasetsVersionsController;
use Neo\Modules\Demographics\Http\Controllers\ExtractsController;
use Neo\Modules\Demographics\Http\Controllers\ExtractsTemplatesController;
use Neo\Modules\Demographics\Http\Controllers\GeographicReportsController;
use Neo\Modules\Demographics\Http\Controllers\GeographicReportsTemplatesController;
use Neo\Modules\Demographics\Http\Controllers\IndexSetsController;
use Neo\Modules\Demographics\Http\Controllers\IndexSetsTemplatesController;
use Neo\Modules\Demographics\Models\DatasetDatapoint;
use Neo\Modules\Demographics\Models\DatasetVersion;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Modules\Demographics\Models\ExtractTemplate;
use Neo\Modules\Demographics\Models\GeographicReport;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;
use Neo\Modules\Demographics\Models\IndexSet;
use Neo\Modules\Demographics\Models\IndexSetTemplate;

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

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1/demographics",
             ],
    static function () {
        /*
        |----------------------------------------------------------------------
        | Datasets & Versions
        |----------------------------------------------------------------------
        */

        Route::model("datapoint", DatasetDatapoint::class);
        Route::model("datasetVersion", DatasetVersion::class);

        Route::get("datasets", [DatasetsController::class, "index"]);

        Route::get("datasets-versions", [DatasetsVersionsController::class, "index"]);
        Route::put("datasets-versions/{datasetVersion}", [DatasetsVersionsController::class, "update"]);

        Route::get("datasets-datapoints", [DatasetsDatapointsController::class, "index"]);
        Route::get("datasets-datapoints/{datapoint}", [DatasetsDatapointsController::class, "show"]);
        Route::put("datasets-datapoints/{datapoint}", [DatasetsDatapointsController::class, "update"]);


        /*
        |----------------------------------------------------------------------
        | Areas
        |----------------------------------------------------------------------
        */

        Route::get("area-types", [AreaTypesController::class, "index"]);

        Route::get("areas", [AreasController::class, "index"]);


        /*
        |----------------------------------------------------------------------
        | Geographic Reports Templates
        |----------------------------------------------------------------------
        */

        Route::model("geographicReportTemplate", GeographicReportTemplate::class);

        Route::   get("geographic-reports-templates", [GeographicReportsTemplatesController::class, "index"]);
        Route::  post("geographic-reports-templates", [GeographicReportsTemplatesController::class, "store"]);
        Route::   get("geographic-reports-templates/{geographicReportTemplate}", [GeographicReportsTemplatesController::class, "show"]);
        Route::   put("geographic-reports-templates/{geographicReportTemplate}", [GeographicReportsTemplatesController::class, "update"]);
        Route::delete("geographic-reports-templates/{geographicReportTemplate}", [GeographicReportsTemplatesController::class, "destroy"]);


        /*
        |----------------------------------------------------------------------
        | Geographic Reports
        |----------------------------------------------------------------------
        */

        Route::model("geographicReport", GeographicReport::class);

        Route::   get("geographic-reports", [GeographicReportsController::class, "index"]);
        Route::  post("geographic-reports", [GeographicReportsController::class, "store"]);
        Route::   get("geographic-reports/{geographicReport}", [GeographicReportsController::class, "show"]);
        Route::delete("geographic-reports/{geographicReport}", [GeographicReportsController::class, "destroy"]);


        /*
        |----------------------------------------------------------------------
        | Extracts Templates
        |----------------------------------------------------------------------
        */

        Route::model("extractTemplate", ExtractTemplate::class);

        Route::   get("extracts-templates", [ExtractsTemplatesController::class, "index"]);
        Route::  post("extracts-templates", [ExtractsTemplatesController::class, "store"]);
        Route::   get("extracts-templates/{extractTemplate}", [ExtractsTemplatesController::class, "show"]);
        Route::   put("extracts-templates/{extractTemplate}", [ExtractsTemplatesController::class, "update"]);
        Route::delete("extracts-templates/{extractTemplate}", [ExtractsTemplatesController::class, "destroy"]);

        /*
        |----------------------------------------------------------------------
        | Extracts
        |----------------------------------------------------------------------
        */

        Route::model("extract", Extract::class);

        Route::   get("extracts", [ExtractsController::class, "index"]);
        Route::  post("extracts", [ExtractsController::class, "store"]);
        Route::   get("extracts/{extractTemplate}", [ExtractsController::class, "show"]);
        Route::delete("extracts/{extractTemplate}", [ExtractsController::class, "destroy"]);

        /*
        |----------------------------------------------------------------------
        | Extracts
        |----------------------------------------------------------------------
        */

        Route::model("indexSetTemplate", IndexSetTemplate::class);

        Route::   get("index-sets-templates", [IndexSetsTemplatesController::class, "index"]);
        Route::  post("index-sets-templates", [IndexSetsTemplatesController::class, "store"]);
        Route::   get("index-sets-templates/{indexSetTemplate}", [IndexSetsTemplatesController::class, "show"]);
        Route::   put("index-sets-templates/{indexSetTemplate}", [IndexSetsTemplatesController::class, "update"]);
        Route::delete("index-sets-templates/{indexSetTemplate}", [IndexSetsTemplatesController::class, "destroy"]);

        /*
        |----------------------------------------------------------------------
        | Extracts
        |----------------------------------------------------------------------
        */

        Route::model("indexSet", IndexSet::class);

        Route::   get("index-sets", [IndexSetsController::class, "index"]);
        Route::  post("index-sets", [IndexSetsController::class, "store"]);
        Route::   get("index-sets/{indexSet}", [IndexSetsController::class, "show"]);
        Route::delete("index-sets/{indexSet}", [IndexSetsController::class, "destroy"]);
    });
