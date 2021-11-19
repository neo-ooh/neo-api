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
use Neo\Http\Requests\ProductCategories\ListProductCategoriesRequest;
use Neo\Http\Requests\ProductCategories\ShowProductCategoryRequest;
use Neo\Models\ProductCategory;

class ProductCategoriesController {
    public function index(ListProductCategoriesRequest $request) {
        $relations         = $request->input("with", []);
        $productCategories = ProductCategory::all();

        return new Response($productCategories);
    }

    public function store() {
        //
    }

    public function show(ShowProductCategoryRequest $request, ProductCategory $productCategory) {
        $relations = $request->input("with", []);

        return new Response($productCategory);
    }

    public function update() {
        //
    }

    public function destroy() {
        //
    }
}
