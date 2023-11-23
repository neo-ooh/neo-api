<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MarketsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Neo\Http\Requests\Markets\DestroyMarketRequest;
use Neo\Http\Requests\Markets\ListMarketsByIdsRequest;
use Neo\Http\Requests\Markets\StoreMarketRequest;
use Neo\Http\Requests\Markets\UpdateMarketRequest;
use Neo\Models\Market;

class MarketsController extends Controller {
	public function byIds(ListMarketsByIdsRequest $request) {
		return new Response(Market::query()->findMany($request->input("ids")));
	}

	public function store(StoreMarketRequest $request) {
		$market              = new Market();
		$market->name_fr     = $request->input("name_fr");
		$market->name_en     = $request->input("name_en");
		$market->province_id = $request->input("province_id");
		$market->area        = $request->input("area", null) !== null ? Polygon::fromJson(json_encode($request->input("area"))) : null;

		$market->save();

		return new Response($market, 201);
	}

	public function update(UpdateMarketRequest $request, Market $market) {
		$market->name_fr = $request->input("name_fr");
		$market->name_en = $request->input("name_en");
		$market->area    = $request->input("area", null) !== null ? Polygon::fromJson(json_encode($request->input("area"))) : null;
		$market->save();

		return new Response($market);
	}

	public function destroy(DestroyMarketRequest $request, Market $market) {
		$market->delete();

		return new Response();
	}
}
