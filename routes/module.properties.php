<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.properties.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\AttachmentsController;
use Neo\Http\Controllers\BrandsController;
use Neo\Http\Controllers\CitiesController;
use Neo\Http\Controllers\CountriesController;
use Neo\Http\Controllers\FieldsCategoriesController;
use Neo\Http\Controllers\FieldsController;
use Neo\Http\Controllers\FieldSegmentsController;
use Neo\Http\Controllers\LoopConfigurationsController;
use Neo\Http\Controllers\MarketsController;
use Neo\Http\Controllers\OpeningHoursController;
use Neo\Http\Controllers\PricelistProductsCategoriesController;
use Neo\Http\Controllers\PricelistsController;
use Neo\Http\Controllers\PricelistsPropertiesController;
use Neo\Http\Controllers\ProductCategoriesController;
use Neo\Http\Controllers\ProductsController;
use Neo\Http\Controllers\ProductsLocationsController;
use Neo\Http\Controllers\ProductTypesController;
use Neo\Http\Controllers\PropertiesContactsController;
use Neo\Http\Controllers\PropertiesController;
use Neo\Http\Controllers\PropertiesDataController;
use Neo\Http\Controllers\PropertiesFieldsSegmentsController;
use Neo\Http\Controllers\PropertiesStatisticsController;
use Neo\Http\Controllers\PropertiesStatsController;
use Neo\Http\Controllers\PropertiesTenantsController;
use Neo\Http\Controllers\PropertiesTrafficController;
use Neo\Http\Controllers\PropertyPicturesController;
use Neo\Http\Controllers\ProvincesController;
use Neo\Http\Controllers\TrafficSnapshotsController;
use Neo\Http\Controllers\TrafficSourcesController;
use Neo\Models\Attachment;
use Neo\Models\Brand;
use Neo\Models\Field;
use Neo\Models\FieldsCategory;
use Neo\Models\FieldSegment;
use Neo\Models\Pricelist;
use Neo\Models\PricelistProductsCategory;
use Neo\Models\Product;
use Neo\Models\ProductCategory;
use Neo\Models\ProductType;
use Neo\Models\TrafficSource;
use Neo\Modules\Broadcast\Models\LoopConfiguration;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ], function () {
    /*
    |----------------------------------------------------------------------
    | Properties
    |----------------------------------------------------------------------
    */

    Route::   get("properties", PropertiesController::class . "@index");
    Route::   get("properties/_by_id", PropertiesController::class . "@byId");
    Route::  post("properties", PropertiesController::class . "@store");
    Route::   get("properties/_networkDump", PropertiesController::class . "@networkDump");
    Route::   get("properties/_need_attention", PropertiesController::class . "@needAttention");
    Route::   get("properties/_search", PropertiesController::class . "@search");
    Route::   get("properties/{propertyId}", PropertiesController::class . "@show")->whereNumber("propertyId");
    Route::   put("properties/{property}", PropertiesController::class . "@update");
    Route::   put("properties/{property}/_mark_reviewed", PropertiesController::class . "@markReviewed");
    Route::   put("properties/{property}/address", PropertiesController::class . "@updateAddress");
    Route::delete("properties/{property}", PropertiesController::class . "@destroy");

    Route::get("properties/{property}/_dump", PropertiesController::class . "@dump");

    Route::   get("properties-statistics/{actor}", PropertiesStatsController::class . "@show");


    /*
    |----------------------------------------------------------------------
    | Properties Traffic
    |----------------------------------------------------------------------
    */

    Route::   get("properties/{property}/traffic", PropertiesTrafficController::class . "@index");
    Route::   put("properties/{property}/traffic", PropertiesTrafficController::class . "@update");
    Route::  post("properties/{property}/traffic", PropertiesTrafficController::class . "@store");

    Route::  get("properties/{property}/statistics", PropertiesStatisticsController::class . "@show");

    /*
    |----------------------------------------------------------------------
    | Traffic Snapshot
    |----------------------------------------------------------------------
    */

    Route::  post("traffic/_refresh_snapshot", TrafficSnapshotsController::class . "@refresh");

    /*
    |----------------------------------------------------------------------
    | Traffic Sources
    |----------------------------------------------------------------------
    */

    Route::model("trafficSource", TrafficSource::class);

    Route::   get("traffic-sources", TrafficSourcesController::class . "@index");
    Route::  post("traffic-sources", TrafficSourcesController::class . "@store");
    Route::   put("traffic-sources/{trafficSource}", TrafficSourcesController::class . "@update");
    Route::delete("traffic-sources/{trafficSource}", TrafficSourcesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Properties Data
    |----------------------------------------------------------------------
    */

    Route::   put("properties/{property}/data", PropertiesDataController::class . "@update");

    /*
    |----------------------------------------------------------------------
    | Properties Pictures
    |----------------------------------------------------------------------
    */

    Route::   get("properties/{property}/pictures", PropertyPicturesController::class . "@index");
    Route::  post("properties/{property}/pictures", PropertyPicturesController::class . "@store");
    Route::   put("properties/{property}/pictures/{propertyPicture}", PropertyPicturesController::class . "@update");
    Route::delete("properties/{property}/pictures/{propertyPicture}", PropertyPicturesController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Opening Hours
    |----------------------------------------------------------------------
    */

    Route::  get("properties/{property}/contacts", PropertiesContactsController::class . "@show");
    Route:: post("properties/{property}/contacts", PropertiesContactsController::class . "@store");
    Route::  put("properties/{property}/contacts/{user}", PropertiesContactsController::class . "@update");
    Route::delete("properties/{property}/contacts/{user}", PropertiesContactsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Opening Hours
    |----------------------------------------------------------------------
    */

    Route::post("properties/{property}/opening-hours/_refresh", OpeningHoursController::class . "@refresh");
    Route::put("properties/{property}/opening-hours/{weekday}", OpeningHoursController::class . "@update");

    /*
    |----------------------------------------------------------------------
    | Addresses
    |----------------------------------------------------------------------
    */

    Route::   get("countries", CountriesController::class . "@index");
    Route::   get("countries/{country}", CountriesController::class . "@show");

    // Provinces
    Route::   get("countries/{country}/provinces", ProvincesController::class . "@index");
    Route::   get("countries/{country}/provinces/{province}", ProvincesController::class . "@show");

    // Markets
    Route::   put("markets/{market}", MarketsController::class . "@update");
    Route::  post("countries/{country}/provinces/{province}/markets", MarketsController::class . "@store");
    Route::delete("markets/{market}", MarketsController::class . "@destroy");

    // Cities
    Route::   get("countries/{country}/provinces/{province}/cities", CitiesController::class . "@index");
    Route::  post("countries/{country}/provinces/{province}/cities", CitiesController::class . "@store");
    Route::   put("countries/{country}/provinces/{province}/cities/{city}", CitiesController::class . "@update");
    Route::delete("countries/{country}/provinces/{province}/cities/{city}", CitiesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Fields
    |----------------------------------------------------------------------
    */

    Route::model("fieldsCategory", FieldsCategory::class);
    Route::model("field", Field::class);
    Route::model("segment", FieldSegment::class);

    Route::   get("fields-categories", FieldsCategoriesController::class . "@index");
    Route::  post("fields-categories", FieldsCategoriesController::class . "@store");
    Route::   get("fields-categories/_by_id", FieldsCategoriesController::class . "@byId");
    Route::   put("fields-categories/{fieldsCategory}", FieldsCategoriesController::class . "@update");
    Route::put("fields-categories/{fieldsCategory}/_reorder", FieldsCategoriesController::class . "@reorder");
    Route::delete("fields-categories/{fieldsCategory}", FieldsCategoriesController::class . "@destroy");

    Route::   get("fields", FieldsController::class . "@index");
    Route::  post("fields", FieldsController::class . "@store");
    Route::   get("fields/{field}", FieldsController::class . "@show");
    Route::   put("fields/{field}", FieldsController::class . "@update");
    Route::delete("fields/{field}", FieldsController::class . "@destroy");

    Route::  post("fields/{field}/segments", FieldSegmentsController::class . "@store");
    Route::   put("fields/{field}/segments/{segment}", FieldSegmentsController::class . "@update");
    Route::delete("fields/{field}/segments/{segment}", FieldSegmentsController::class . "@destroy");

    Route::  post("properties/{property}/fields/{field}", PropertiesFieldsSegmentsController::class . "@store");
    Route::delete("properties/{property}/fields/{field}", PropertiesFieldsSegmentsController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Products
    |----------------------------------------------------------------------
    */

    Route::model("product", Product::class);
    Route::model("productCategory", ProductCategory::class);
    Route::model("productType", ProductType::class);

    Route:: get("product-types", ProductTypesController::class . "@index");
    Route:: get("product-types/_by_id", ProductTypesController::class . "@byIds");
    Route:: put("product-types/{productType}", ProductTypesController::class . "@update");

    Route:: get("product-categories", ProductCategoriesController::class . "@index");
    Route:: get("product-categories/_by_id", ProductCategoriesController::class . "@byIds");
    Route:: get("product-categories/{productCategory}", ProductCategoriesController::class . "@show");
    Route:: put("product-categories/{productCategory}", ProductCategoriesController::class . "@update");

    Route::get("products", ProductsController::class . "@index");
    Route::get("products/_by_id", ProductsController::class . "@byIds");
    Route::get("products/{product}", ProductsController::class . "@show");
    Route::put("products/{product}", ProductsController::class . "@update");

    Route:: put("products/{product}/locations", ProductsLocationsController::class . "@sync");

    /*
    |----------------------------------------------------------------------
    | Products attachments
    |----------------------------------------------------------------------
    */

    Route::model("attachment", Attachment::class);

    Route::  post("product-categories/{productCategory}/attachments", AttachmentsController::class . "@storeProductCategory");
    Route::   put("product-categories/{productCategory}/attachments/{attachment}", AttachmentsController::class . "@updateProductCategory");
    Route::delete("product-categories/{productCategory}/attachments/{attachment}", AttachmentsController::class . "@destroyProductCategory");

    Route::  post("products/{product}/attachments", AttachmentsController::class . "@storeProduct");
    Route::   put("products/{product}/attachments/{attachment}", AttachmentsController::class . "@updateProduct");
    Route::delete("products/{product}/attachments/{attachment}", AttachmentsController::class . "@destroyProduct");


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

    /*
    |----------------------------------------------------------------------
    | Brands & Property Tenants
    |----------------------------------------------------------------------
    */

    Route::model("brand", Brand::class);

    Route::   get("brands", BrandsController::class . "@index");
    Route::  post("brands", BrandsController::class . "@store");
    Route::  post("brands/_batch", BrandsController::class . "@storeBatch");
    Route::  post("brands/{brand}/_sync_children", BrandsController::class . "@syncChildren");
    Route::   get("brands/{brand}", BrandsController::class . "@show");
    Route::   put("brands/{brand}", BrandsController::class . "@update");
    Route::   post("brands/{brand}/_merge", BrandsController::class . "@merge");
    Route::delete("brands/{brand}", BrandsController::class . "@destroy");

    Route:: get("properties/{property}/tenants", PropertiesTenantsController::class . "@index");
    Route::post("properties/{property}/tenants", PropertiesTenantsController::class . "@sync");


    /*
    |----------------------------------------------------------------------
    | Price lists
    |----------------------------------------------------------------------
    */

    Route::model("pricelist", Pricelist::class);
    Route::model("pricelistProductsCategory", Neo\Models\PricelistProductsCategory::class, function ($value) {
        return PricelistProductsCategory::query()
                                        ->where("products_category_id", "=", $value)
                                        ->where("pricelist_id", "=", Request::route()?->parameter("pricelist"))
                                        ->first();
    });

    Route::   get("pricelists", PricelistsController::class . "@index");
    Route::  post("pricelists", PricelistsController::class . "@store");
    Route::   get("pricelists/_by_id", PricelistsController::class . "@byIds");
    Route::   get("pricelists/{pricelist}", PricelistsController::class . "@show");
    Route::   put("pricelists/{pricelist}", PricelistsController::class . "@update");
    Route::delete("pricelists/{pricelist}", PricelistsController::class . "@destroy");

    Route::   get("pricelists/{pricelist}/product-categories", PricelistProductsCategoriesController::class . "@index");
    Route::  post("pricelists/{pricelist}/product-categories", PricelistProductsCategoriesController::class . "@store");
    Route::   get("pricelists/{pricelist}/product-categories/{pricelistProductsCategory}", PricelistProductsCategoriesController::class . "@show");
    Route::   put("pricelists/{pricelist}/product-categories/{pricelistProductsCategory}", PricelistProductsCategoriesController::class . "@update");
    Route::delete("pricelists/{pricelist}/product-categories/{pricelistProductsCategory}", PricelistProductsCategoriesController::class . "@destroy");

    Route::   get("pricelists/{pricelist}/properties", PricelistsPropertiesController::class . "@index");
    Route::   put("pricelists/{pricelist}/properties", PricelistsPropertiesController::class . "@sync");
});
