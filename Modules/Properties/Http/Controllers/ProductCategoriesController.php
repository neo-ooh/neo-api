<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductCategoriesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Http\Requests\ProductCategories\ListProductCategoriesByIdsRequest;
use Neo\Modules\Properties\Http\Requests\ProductCategories\ListProductCategoriesRequest;
use Neo\Modules\Properties\Http\Requests\ProductCategories\ShowProductCategoryRequest;
use Neo\Modules\Properties\Http\Requests\ProductCategories\UpdateProductCategoryRequest;
use Neo\Modules\Properties\Models\ProductCategory;

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
        $productCategory->name_en = $request->input("name_en");
        $productCategory->name_fr = $request->input("name_fr");
        $productCategory->type    = $request->input("type");

        $productCategory->format_id = $request->input("format_id");

        $productCategory->allowed_media_types = $request->has("allowed_media_types")
            ? array_map(static fn(string $scope) => MediaType::from($scope), $request->input("allowed_media_types", []))
            : $productCategory->allowed_media_types;
        $productCategory->allows_audio        = $request->input("allows_audio", $productCategory->allows_audio);
        $productCategory->allows_motion       = $request->input("allows_motion", $productCategory->allows_motion);

        $productCategory->screen_size_in = $request->input("screen_size_in", $productCategory->screen_size_in);
        $productCategory->screen_type_id = $request->input("screen_type_id", $productCategory->screen_type_id);

        $productCategory->production_cost    = $request->input("production_cost", $productCategory->production_cost);
        $productCategory->programmatic_price = $request->input("programmatic_price", $productCategory->programmatic_price);
        $productCategory->save();

        return new Response($productCategory->loadPublicRelations());
    }

    public function destroy() {
        //
    }
}
