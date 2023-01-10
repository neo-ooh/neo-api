<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Products\ListProductsByIdsRequest;
use Neo\Http\Requests\Products\ListProductsRequest;
use Neo\Http\Requests\Products\ShowProductRequest;
use Neo\Http\Requests\Products\UpdateProductRequest;
use Neo\Models\Product;

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
        $product->format_id = $request->input("format_id");
        $product->save();

        return new Response($product->loadPublicRelations());
    }
}
