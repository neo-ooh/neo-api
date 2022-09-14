<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PricelistsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PriceList\DestroyPricelistRequest;
use Neo\Http\Requests\PriceList\ListPricelistsByIdsRequest;
use Neo\Http\Requests\PriceList\ListPricelistsRequest;
use Neo\Http\Requests\PriceList\ShowPricelistRequest;
use Neo\Http\Requests\PriceList\StorePricelistRequest;
use Neo\Http\Requests\PriceList\UpdatePricelistRequest;
use Neo\Models\Pricelist;

class PricelistsController {
    public function index(ListPricelistsRequest $request): Response {
        $priceLists = Pricelist::query()->orderBy("name")->get();

        return new Response($priceLists);
    }

    public function byIds(ListPricelistsByIdsRequest $request): Response {
        $priceLists = Pricelist::query()->whereIn("id", $request->input("ids", []))
                               ->orderBy("name")
                               ->with(["categories"])
                               ->get();
        return new Response($priceLists);
    }

    public function store(StorePricelistRequest $request): Response {
        $priceList = new Pricelist([
            "name"        => $request->input("name"),
            "description" => $request->input("description"),
        ]);

        $priceList->save();

        return new Response($priceList, 201);
    }

    public function show(ShowPricelistRequest $request, Pricelist $pricelist): Response {
        return new Response($pricelist->load(["categories", "products.property"]));
    }

    public function update(UpdatePricelistRequest $request, Pricelist $pricelist): Response {
        $pricelist->name        = $request->input("name");
        $pricelist->description = $request->input("description");
        $pricelist->save();

        return new Response($pricelist);
    }

    public function destroy(DestroyPricelistRequest $request, Pricelist $pricelist): Response {
        $pricelist->delete();

        return new Response(["status" => "ok"]);
    }
}
