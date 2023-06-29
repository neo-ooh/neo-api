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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Models\Utils\ActorsGetter;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Http\Requests\Products\DestroyProductRequest;
use Neo\Modules\Properties\Http\Requests\Products\ListProductsByIdsRequest;
use Neo\Modules\Properties\Http\Requests\Products\ListProductsRequest;
use Neo\Modules\Properties\Http\Requests\Products\ShowProductRequest;
use Neo\Modules\Properties\Http\Requests\Products\UpdateProductRequest;
use Neo\Modules\Properties\Models\Product;

class ProductsController {
    public function index(ListProductsRequest $request) {
        $query = Product::query()
                        ->when($request->has("property_id"), function (Builder $query) use ($request) {
                            $query->where("property_id", "=", $request->input("property_id"));
                        })
                        ->when($request->has("category_id"), function (Builder $query) use ($request) {
                            $query->where("category_id", "=", $request->input("category_id"));
                        })
                        ->when($request->has("type"), function (Builder $query) use ($request) {
                            $query->whereHas("category", function (Builder $query) use ($request) {
                                $query->where("type", "=", $request->enum("type", ProductType::class));
                            });
                        })
                        ->when($request->input("bonus") !== null, function (Builder $query) use ($request) {
                            $query->where("is_bonus", "=", $request->input("bonus"));
                        });

        if ($request->has("parent_id")) {
            $actorIds = ActorsGetter::from($request->input("parent_id"))
                                    ->selectChildren(recursive: true)
                                    ->getSelection();

            $query->whereIn("property_id", $actorIds);
        }

        return new Response($query->get()->loadPublicRelations());
    }

    public function show(ShowProductRequest $request, Product $product) {
        return new Response($product->loadPublicRelations());
    }

    public function byIds(ListProductsByIdsRequest $request) {
        $products = Product::query()->whereIn("id", $request->input("ids"))->get();

        return new Response($products->loadPublicRelations());
    }

    public function update(UpdateProductRequest $request, Product $product) {
        $product->is_sellable = $request->input("is_sellable");

        $product->format_id    = $request->input("format_id");
        $product->site_type_id = $request->input("site_type_id");

        $product->allowed_media_types = $request->has("allowed_media_types")
            ? array_map(static fn(string $scope) => MediaType::from($scope), $request->input("allowed_media_types", []))
            : $product->allowed_media_types;
        $product->allows_audio        = $request->input("allows_audio", $product->allows_audio);
        $product->allows_motion       = $request->input("allows_motion", $product->allows_motion);

        $product->screen_size_in = $request->input("screen_size_in", $product->screen_size_in);
        $product->screen_type_id = $request->input("screen_type_id", $product->screen_type_id);

        $product->production_cost    = $request->input("production_cost", $product->production_cost);
        $product->programmatic_price = $request->input("programmatic_price");

        $product->cover_picture_id = $request->input("cover_picture_id");
        $product->notes            = $request->input("notes") ?? "";

        $product->save();

        return new Response($product->loadPublicRelations());
    }

    public function destroy(DestroyProductRequest $request, Product $product) {
        $product->delete();

        return new Response([], 202);
    }
}
