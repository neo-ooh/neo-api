<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - products.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\AttachmentsController;
use Neo\Modules\Properties\Http\Controllers\BrandsController;
use Neo\Modules\Properties\Http\Controllers\MobileProductsBracketsController;
use Neo\Modules\Properties\Http\Controllers\MobileProductsController;
use Neo\Modules\Properties\Http\Controllers\PricelistProductsCategoriesController;
use Neo\Modules\Properties\Http\Controllers\PricelistProductsController;
use Neo\Modules\Properties\Http\Controllers\PricelistsController;
use Neo\Modules\Properties\Http\Controllers\PricelistsPropertiesController;
use Neo\Modules\Properties\Http\Controllers\ProductCategoriesController;
use Neo\Modules\Properties\Http\Controllers\ProductsController;
use Neo\Modules\Properties\Http\Controllers\ProductsLocationsController;
use Neo\Modules\Properties\Http\Controllers\PropertiesTenantsController;
use Neo\Modules\Properties\Http\Controllers\ScreenTypesController;
use Neo\Modules\Properties\Models\Attachment;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\MobileProduct;
use Neo\Modules\Properties\Models\MobileProductBracket;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;

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

		Route::   get("product-categories", ProductCategoriesController::class . "@index");
		Route::   get("product-categories/_by_id", ProductCategoriesController::class . "@byIds");
		Route::   get("product-categories/{productCategory}", ProductCategoriesController::class . "@show");
		Route::   put("product-categories/{productCategory}", ProductCategoriesController::class . "@update");

		Route::   get("products", [ProductsController::class, "index"]);
		Route::   get("products/_by_id", [ProductsController::class, "byIds"]);
		Route::   get("products/{product}", [ProductsController::class, "show"]);
		Route::   put("products/{product}", [ProductsController::class, "update"]);
		Route::delete("products/{product}", [ProductsController::class, "destroy"]);

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

		Route::   get("properties/{property}/tenants", [PropertiesTenantsController::class, "index"]);
		Route::  post("properties/{property}/tenants", [PropertiesTenantsController::class, "sync"]);
		Route::   put("properties/{property}/tenants", [PropertiesTenantsController::class, "import"]);
		Route::delete("properties/{property}/tenants/{brand}", [PropertiesTenantsController::class, "remove"]);


		/*
		|----------------------------------------------------------------------
		| Pricelists
		|----------------------------------------------------------------------
		*/

		Route::model("pricelist", Pricelist::class);

		Route::   get("pricelists", PricelistsController::class . "@index");
		Route::  post("pricelists", PricelistsController::class . "@store");
		Route::   get("pricelists/_by_id", PricelistsController::class . "@byIds");
		Route::   get("pricelists/{pricelist}", PricelistsController::class . "@show");
		Route::   put("pricelists/{pricelist}", PricelistsController::class . "@update");
		Route::delete("pricelists/{pricelist}", PricelistsController::class . "@destroy");

		Route::   get("pricelists/{pricelist}/product-categories", PricelistProductsCategoriesController::class . "@index");
		Route::  post("pricelists/{pricelist}/product-categories", PricelistProductsCategoriesController::class . "@store");
		Route::   get("pricelists/{pricelist}/product-categories/{pricelistProductsCategory:id}", PricelistProductsCategoriesController::class . "@show");
		Route::   put("pricelists/{pricelist}/product-categories/{pricelistProductsCategory:id}", PricelistProductsCategoriesController::class . "@update");
		Route::delete("pricelists/{pricelist}/product-categories/{pricelistProductsCategory:id}", PricelistProductsCategoriesController::class . "@destroy");

		Route::   get("pricelists/{pricelist}/products", [PricelistProductsController::class, "index"]);
		Route::  post("pricelists/{pricelist}/products", [PricelistProductsController::class, "store"]);
		Route::   get("pricelists/{pricelist}/products/{pricelistProduct:id}", [PricelistProductsController::class, "show"]);
		Route::   put("pricelists/{pricelist}/products/{pricelistProduct:id}", [PricelistProductsController::class, "update"]);
		Route::delete("pricelists/{pricelist}/products/{pricelistProduct:id}", [PricelistProductsController::class, "destroy"]);

		Route::   get("pricelists/{pricelist}/properties", PricelistsPropertiesController::class . "@index");
		Route::   put("pricelists/{pricelist}/properties", PricelistsPropertiesController::class . "@sync");


		/*
		|----------------------------------------------------------------------
		| Screen Types
		|----------------------------------------------------------------------
		*/

		Route::   get("screen-types", [ScreenTypesController::class, "index"]);
		Route::  post("screen-types", [ScreenTypesController::class, "store"]);
		Route::   get("screen-types/{screenType}", [ScreenTypesController::class, "show"]);
		Route::   put("screen-types/{screenType}", [ScreenTypesController::class, "update"]);
		Route::delete("screen-types/{screenType}", [ScreenTypesController::class, "destroy"]);


		/*
		|----------------------------------------------------------------------
		| Mobile Products
		|----------------------------------------------------------------------
		*/

		Route::model("mobileProduct", MobileProduct::class);
		Route::model("mobileProductBracket", MobileProductBracket::class);

		Route::   get("mobile-products", [MobileProductsController::class, "index"]);
		Route::  post("mobile-products", [MobileProductsController::class, "store"]);
		Route::   get("mobile-products/{mobileProduct}", [MobileProductsController::class, "show"]);
		Route::   put("mobile-products/{mobileProduct}", [MobileProductsController::class, "update"]);
		Route::delete("mobile-products/{mobileProduct}", [MobileProductsController::class, "delete"]);

		Route::   get("mobile-products/{mobileProduct}/brackets", [MobileProductsBracketsController::class, "index"]);
		Route::  post("mobile-products/{mobileProduct}/brackets", [MobileProductsBracketsController::class, "store"]);
		Route::   get("mobile-products/{mobileProduct}/brackets/{mobileProductBracket}", [MobileProductsBracketsController::class, "show"]);
		Route::   put("mobile-products/{mobileProduct}/brackets/{mobileProductBracket}", [MobileProductsBracketsController::class, "update"]);
		Route::delete("mobile-products/{mobileProduct}/brackets/{mobileProductBracket}", [MobileProductsBracketsController::class, "delete"]);
	});
