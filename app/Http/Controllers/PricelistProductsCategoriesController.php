<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistProductsCategoriesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PricelistProductsCategories\DestroyPricelistProductsCategoryRequest;
use Neo\Http\Requests\PricelistProductsCategories\ListPricelistProductsCategoriesRequest;
use Neo\Http\Requests\PricelistProductsCategories\StorePricelistProductCategoryRequest;
use Neo\Http\Requests\PricelistProductsCategories\UpdatePricelistProductCategoryRequest;
use Neo\Http\Requests\PropertiesStatistics\ShowPropertiesStatisticsRequest;
use Neo\Models\Pricelist;
use Neo\Models\pricelistProductsCategory;

class PricelistProductsCategoriesController {
    public function index(ListPricelistProductsCategoriesRequest $request, Pricelist $pricelist) {
        $categories = $pricelist->categories;

        return new Response($categories);
    }

    public function store(StorePricelistProductCategoryRequest $request, Pricelist $pricelist) {
        $categoryPricelist = new PricelistProductsCategory([
            "pricelist_id"         => $pricelist,
            "products_category_id" => $request->input("products_category_id"),
            "pricing"              => $request->input("pricing"),
            "value"                => $request->input("value"),
            "min"                  => $request->input("min", null),
            "max"                  => $request->input("max", null),
        ]);

        $categoryPricelist->save();

        return new Response($categoryPricelist, 201);
    }

    public function show(ShowPropertiesStatisticsRequest $request, PricelistProductsCategory $pricelistProductsCategory) {
        return new Response($pricelistProductsCategory);
    }

    public function update(UpdatePricelistProductCategoryRequest $request, PricelistProductsCategory $pricelistProductsCategory) {
        $pricelistProductsCategory->fill([
            "products_category_id" => $request->input("products_category_id"),
            "pricing"              => $request->input("pricing"),
            "value"                => $request->input("value"),
            "min"                  => $request->input("min", null),
            "max"                  => $request->input("max", null),
        ]);
        $pricelistProductsCategory->save();

        return new Response($pricelistProductsCategory);
    }

    public function destroy(DestroyPricelistProductsCategoryRequest $request, PricelistProductsCategory $pricelistProductsCategory) {
        $pricelistProductsCategory->delete();

        return new Response(["status" => "ok"]);
    }
}
