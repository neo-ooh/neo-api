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
use Neo\Models\PricelistProductsCategory;

class PricelistProductsCategoriesController {
    public function index(ListPricelistProductsCategoriesRequest $request, Pricelist $pricelist) {
        $categories = PricelistProductsCategory::query()->where("pricelist_id", "=", $pricelist->getKey())
                                               ->get();

        return new Response($categories);
    }

    public function store(StorePricelistProductCategoryRequest $request, Pricelist $pricelist) {
        $categoryPricelist = new PricelistProductsCategory([
            "pricelist_id"         => $pricelist->getKey(),
            "products_category_id" => $request->input("products_category_id"),
            "pricing"              => $request->input("pricing"),
            "value"                => $request->input("value"),
            "min"                  => $request->input("min", null),
            "max"                  => $request->input("max", null),
        ]);

        $categoryPricelist->save();

        return new Response($categoryPricelist, 201);
    }

    public function show(ShowPropertiesStatisticsRequest $request, Pricelist $pricelist, PricelistProductsCategory $pricelistProductsCategory) {
        return new Response($pricelistProductsCategory);
    }

    public function update(UpdatePricelistProductCategoryRequest $request, Pricelist $pricelist, PricelistProductsCategory $pricelistProductsCategory) {
        $pricelist->categories()->updateExistingPivot($pricelistProductsCategory->products_category_id, [
            "pricing" => $request->input("pricing"),
            "value"   => $request->input("value"),
            "min"     => $request->input("min", null),
            "max"     => $request->input("max", null),
        ]);

        return new Response($pricelist->categories()->firstWhere("id", "=", $pricelistProductsCategory->products_category_id));
    }

    public function destroy(DestroyPricelistProductsCategoryRequest $request, Pricelist $pricelist, PricelistProductsCategory $pricelistProductsCategory) {
        $pricelist->categories()->detach($pricelistProductsCategory->products_category_id);

        return new Response(["status" => "ok"]);
    }
}
