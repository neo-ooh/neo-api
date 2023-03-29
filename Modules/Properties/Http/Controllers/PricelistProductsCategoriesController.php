<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistProductsCategoriesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\PricelistProductsCategories\DestroyPricelistProductsCategoryRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProductsCategories\ListPricelistProductsCategoriesRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProductsCategories\ShowPricelistProductsCategoryRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProductsCategories\StorePricelistProductCategoryRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProductsCategories\UpdatePricelistProductCategoryRequest;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\PricelistProductsCategory;
use Neo\Modules\Properties\Models\ProductCategory;

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

    public function show(ShowPricelistProductsCategoryRequest $request, Pricelist $pricelist, ProductCategory $pricelistProductsCategory) {
        return new Response($pricelistProductsCategory->pricing);
    }

    public function update(UpdatePricelistProductCategoryRequest $request, Pricelist $pricelist, ProductCategory $pricelistProductsCategory) {
        $pricelist->categories()->updateExistingPivot($pricelistProductsCategory->getKey(), [
            "pricing" => $request->input("pricing"),
            "value"   => $request->input("value"),
            "min"     => $request->input("min", null),
            "max"     => $request->input("max", null),
        ]);

        return new Response($pricelist->categories()->firstWhere("id", "=", $pricelistProductsCategory->getKey()));
    }

    public function destroy(DestroyPricelistProductsCategoryRequest $request, Pricelist $pricelist, ProductCategory $pricelistProductsCategory) {
        $pricelistProductsCategory->pricing()->delete();

        return new Response(["status" => "ok"]);
    }
}
