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
use Neo\Http\Requests\ProductCategories\UpdateProductCategoryRequest;
use Neo\Models\ProductCategory;

class ProductCategoriesController {
    public function index(ListProductCategoriesRequest $request) {
        $relations         = $request->input("with", []);
        $productCategories = ProductCategory::all();

        if (in_array("impressions_models", $relations, true)) {
            $productCategories->loadMissing("impressions_models");
        }

        if (in_array("product_type", $relations, true)) {
            $productCategories->loadMissing("product_type");
        }

        if (in_array("attachments", $relations, true)) {
            $productCategories->loadMissing("attachments");
        }

        return new Response($productCategories);
    }

    public function store() {
        //
    }

    public function show(ShowProductCategoryRequest $request, ProductCategory $productCategory) {
        $relations = $request->input("with", []);

        if (in_array("impressions_models", $relations, true)) {
            $productCategory->loadMissing("impressions_models");
        }

        if (in_array("attachments", $relations, true)) {
            $productCategory->loadMissing("attachments");
        }

        if (in_array("products", $relations, true)) {
            $productCategory->loadMissing(["products", "products.property", "products.locations"]);

            if (in_array("impressions_models", $relations, true)) {
                $productCategory->loadMissing("products.impressions_models");
            }

            if (in_array("attachments", $relations, true)) {
                $productCategory->loadMissing("products.attachments");
            }
        }


        return new Response($productCategory);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory) {
        $productCategory->name_en       = $request->input("name_en");
        $productCategory->name_fr       = $request->input("name_fr");
        $productCategory->fill_strategy = $request->input("fill_strategy");
        $productCategory->save();

        return new Response($productCategory);
    }

    public function destroy() {
        //
    }
}
