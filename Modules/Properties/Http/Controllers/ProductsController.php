<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Http\Requests\Products\ListProductsByIdsRequest;
use Neo\Modules\Properties\Http\Requests\Products\ListProductsRequest;
use Neo\Modules\Properties\Http\Requests\Products\ShowProductRequest;
use Neo\Modules\Properties\Http\Requests\Products\UpdateProductRequest;
use Neo\Modules\Properties\Models\Product;

class ProductsController {
    public function index(ListProductsRequest $request) {
        $products = Product::query()
                           ->when($request->has("property_id"), function ($query) use ($request) {
                               $query->where("property_id", "=", $request->input("property_id"));
                           })
                           ->when($request->has("category_id"), function ($query) use ($request) {
                               $query->where("category_id", "=", $request->input("category_id"));
                           })->get();

        return new Response($products->loadPublicRelations());
    }

    public function show(ShowProductRequest $request, Product $product) {
        return new Response($product->loadPublicRelations());
    }

    public function byIds(ListProductsByIdsRequest $request) {
        $products = Product::query()->whereIn("id", $request->input("ids"))->get();

        return new Response($products->loadPublicRelations());
    }

    public function update(UpdateProductRequest $request, Product $product) {
        $product->format_id           = $request->input("format_id");
        $product->allowed_media_types = $request->has("allowed_media_types")
            ? array_map(static fn(string $scope) => MediaType::from($scope), $request->input("allowed_media_types", []))
            : $product->allowed_media_types;
        $product->allows_audio        = $request->input("allows_audio", $product->allows_audio);
        $product->production_cost     = $request->input("production_cost", $product->production_cost);
        $product->save();

        return new Response($product->loadPublicRelations());
    }
}
