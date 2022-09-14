<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistProductsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PricelistProducts\DestroyPricelistProductRequest;
use Neo\Http\Requests\PricelistProducts\ListPricelistProductsRequest;
use Neo\Http\Requests\PricelistProducts\ShowPricelistProductRequest;
use Neo\Http\Requests\PricelistProducts\StorePricelistProductRequest;
use Neo\Http\Requests\PricelistProducts\UpdatePricelistProductRequest;
use Neo\Models\Pricelist;
use Neo\Models\PricelistProduct;

class PricelistProductsController {
    public function index(ListPricelistProductsRequest $request, Pricelist $pricelist) {
        $products = PricelistProduct::query()->where("pricelist_id", "=", $pricelist->getKey())
                                    ->get();

        return new Response($products);
    }

    public function store(StorePricelistProductRequest $request, Pricelist $pricelist) {
        $categoryPricelist = new PricelistProduct([
            "pricelist_id" => $pricelist->getKey(),
            "product_id"   => $request->input("product_id"),
            "pricing"      => $request->input("pricing"),
            "value"        => $request->input("value"),
            "min"          => $request->input("min", null),
            "max"          => $request->input("max", null),
        ]);

        $categoryPricelist->save();

        return new Response($categoryPricelist, 201);
    }

    public function show(ShowPricelistProductRequest $request, Pricelist $pricelist, PricelistProduct $pricelistProduct) {
        return new Response($pricelistProduct);
    }

    public function update(UpdatePricelistProductRequest $request, Pricelist $pricelist, PricelistProduct $pricelistProduct) {
        $pricelist->products()->updateExistingPivot($pricelistProduct->product_id, [
            "pricing" => $request->input("pricing"),
            "value"   => $request->input("value"),
            "min"     => $request->input("min", null),
            "max"     => $request->input("max", null),
        ]);

        return new Response($pricelist->products()->firstWhere("id", "=", $pricelistProduct->product_id));
    }

    public function destroy(DestroyPricelistProductRequest $request, Pricelist $pricelist, PricelistProduct $pricelistProduct) {
        $pricelist->products()->detach($pricelistProduct->product_id);

        return new Response(["status" => "ok"]);
    }
}
