<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistProductsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\PricelistProducts\DestroyPricelistProductRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProducts\ListPricelistProductsRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProducts\ShowPricelistProductRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProducts\StorePricelistProductRequest;
use Neo\Modules\Properties\Http\Requests\PricelistProducts\UpdatePricelistProductRequest;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\PricelistProduct;
use Neo\Modules\Properties\Models\Product;

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

    public function show(ShowPricelistProductRequest $request, Pricelist $pricelist, Product $pricelistProduct) {
        return new Response($pricelistProduct->pricing);
    }

    public function update(UpdatePricelistProductRequest $request, Pricelist $pricelist, Product $pricelistProduct) {
        $pricelist->products()->updateExistingPivot($pricelistProduct->getKey(), [
            "pricing" => $request->input("pricing"),
            "value"   => $request->input("value"),
            "min"     => $request->input("min", null),
            "max"     => $request->input("max", null),
        ]);

        return new Response($pricelist->products()->firstWhere("id", "=", $pricelistProduct->getKey()));
    }

    public function destroy(DestroyPricelistProductRequest $request, Pricelist $pricelist, Product $pricelistProduct) {
        $pricelistProduct->pricing->delete();

        return new Response(["status" => "ok"]);
    }
}
