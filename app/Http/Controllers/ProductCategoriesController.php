<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductCategoriesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ProductCategories\ListProductCategoriesByIdsRequest;
use Neo\Http\Requests\ProductCategories\ListProductCategoriesRequest;
use Neo\Http\Requests\ProductCategories\ShowProductCategoryRequest;
use Neo\Http\Requests\ProductCategories\UpdateProductCategoryRequest;
use Neo\Models\ProductCategory;

class ProductCategoriesController {
    public function index(ListProductCategoriesRequest $request) {
        $productCategories = ProductCategory::all();

        return new Response($productCategories->loadPublicRelations());
    }

    public function byIds(ListProductCategoriesByIdsRequest $request) {
        $productCategories = ProductCategory::query()->findMany($request->input("ids"));

        return new Response($productCategories->loadPublicRelations());
    }

    public function store() {
        //
    }

    public function show(ShowProductCategoryRequest $request, ProductCategory $productCategory) {
        return new Response($productCategory->loadPublicRelations());
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory) {
        $productCategory->name_en       = $request->input("name_en");
        $productCategory->name_fr       = $request->input("name_fr");
        $productCategory->fill_strategy = $request->input("fill_strategy");
        $productCategory->format_id     = $request->input("format_id");
        $productCategory->save();

        return new Response($productCategory->loadPublicRelations());
    }

    public function destroy() {
        //
    }
}
