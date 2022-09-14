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
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\Products\ImportMappingsRequest;
use Neo\Http\Requests\Products\ListProductsRequest;
use Neo\Http\Requests\Products\ShowProductRequest;
use Neo\Models\Location;
use Neo\Models\Product;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ProductsController {
    public function index(ListProductsRequest $request) {
        if ($request->has("property_id")) {
            $products = Product::query()->where("property_id", "=", $request->input("property_id"))->get();
        } else {
            $products = Product::query()->get();
        }

        if (in_array("category", $request->input("with", []))) {
            $products->load("category");
        }

        return new Response($products);
    }

    public function show(ShowProductRequest $request, Product $product) {
        $product->load([
            "attachments",
            "impressions_models",
            "locations",
            "loop_configurations"
        ]);

        return new Response($product);
    }

    public function _importMappings(ImportMappingsRequest $request) {
        $xlsx = new Xlsx();
        $wb   = $xlsx->load($request->file("file")->path());
        $rows = $wb->getActiveSheet()->toArray();

        array_shift($rows);

        $productsIndex  = $request->input("products_col");
        $displayUnitCol = $request->input("display_units_col");

        $idPairs = collect();

        foreach ($rows as $i => $row) {
            if (!$row[$productsIndex] || !$row[$displayUnitCol]) {
                continue;
            }

            $idPairs[] = [$row[$productsIndex], $row[$displayUnitCol]];
        }

        $odooProductsIds = $idPairs->pluck(0)->unique();
        $displayUnitsIds = $idPairs->pluck(1)->unique();

        $products  = Product::query()->setEagerLoads([])->whereIn("external_id", $odooProductsIds)->get();
        $locations = Location::query()->setEagerLoads([])->whereIn("external_id", $displayUnitsIds)->get();


        $pairs   = collect();
        $errored = collect();

        foreach ($idPairs as [$odooId, $displayUnitId]) {
            $product  = $products->firstWhere("external_id", "=", $odooId);
            $location = $locations->firstWhere("external_id", "=", $displayUnitId);

            if (!$product || !$location) {
                clock("Error for pair $odooId => $displayUnitId. ({$product?->getKey()} || {$location?->getKey()})");
                $errored[] = ["product_id" => $odooId, "location_id" => $displayUnitId];
                continue;
            }

            $pairs[] = ["product_id" => $product->getKey(), "location_id" => $location->getKey()];
        }

        DB::table("products_locations")->insertOrIgnore($pairs->toArray());

        return new Response($errored);
    }
}
