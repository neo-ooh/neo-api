<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - properties.php
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\AttachmentsController;
use Neo\Modules\Properties\Http\Controllers\BrandsController;
use Neo\Modules\Properties\Http\Controllers\PricelistProductsCategoriesController;
use Neo\Modules\Properties\Http\Controllers\PricelistsController;
use Neo\Modules\Properties\Http\Controllers\PricelistsPropertiesController;
use Neo\Modules\Properties\Http\Controllers\ProductCategoriesController;
use Neo\Modules\Properties\Http\Controllers\ProductsController;
use Neo\Modules\Properties\Http\Controllers\ProductsLocationsController;
use Neo\Modules\Properties\Http\Controllers\ProductTypesController;
use Neo\Modules\Properties\Http\Controllers\PropertiesTenantsController;
use Neo\Modules\Properties\Models\Attachment;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\PricelistProductsCategory;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\ProductType;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ],
    static function () {

        /*
        |----------------------------------------------------------------------
        | Products
        |----------------------------------------------------------------------
        */

        Route::model("product", Product::class);
        Route::model("productCategory", ProductCategory::class);
        Route::model("productType", ProductType::class);

        Route::   get("product-types", ProductTypesController::class . "@index");
        Route::   get("product-types/_by_id", ProductTypesController::class . "@byIds");
        Route::   put("product-types/{productType}", ProductTypesController::class . "@update");

        Route::   get("product-categories", ProductCategoriesController::class . "@index");
        Route::   get("product-categories/_by_id", ProductCategoriesController::class . "@byIds");
        Route::   get("product-categories/{productCategory}", ProductCategoriesController::class . "@show");
        Route::   put("product-categories/{productCategory}", ProductCategoriesController::class . "@update");

        Route::   get("products", ProductsController::class . "@index");
        Route::   get("products/_by_id", ProductsController::class . "@byIds");
        Route::   get("products/{product}", ProductsController::class . "@show");
        Route::   put("products/{product}", ProductsController::class . "@update");

        Route::   put("products/{product}/locations", ProductsLocationsController::class . "@sync");

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
        Route::  post("brands/{brand}/_merge", BrandsController::class . "@merge");
        Route::delete("brands/{brand}", BrandsController::class . "@destroy");

        Route::   get("properties/{property}/tenants", PropertiesTenantsController::class . "@index");
        Route::  post("properties/{property}/tenants", PropertiesTenantsController::class . "@sync");


        /*
        |----------------------------------------------------------------------
        | Pricelists
        |----------------------------------------------------------------------
        */

        Route::model("pricelist", Pricelist::class);
        Route::model("pricelistProductsCategory", PricelistProductsCategory::class,
            static function ($value) {
                return PricelistProductsCategory::query()
                                                ->where("products_category_id", "=", $value)
                                                ->where("pricelist_id", "=", Request::route()?->originalParameter("pricelist"))
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
