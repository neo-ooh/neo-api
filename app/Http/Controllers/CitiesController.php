<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CitiesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Requests\Cities\DestroyCityRequest;
use Neo\Http\Requests\Cities\ListCitiesByIdsRequest;
use Neo\Http\Requests\Cities\ListCitiesRequest;
use Neo\Http\Requests\Cities\StoreCityRequest;
use Neo\Http\Requests\Cities\UpdateCityRequest;
use Neo\Jobs\PullCityGeolocationJob;
use Neo\Models\City;

class CitiesController extends Controller {
	public function index(ListCitiesRequest $request): Response {
		return new Response(City::query()
		                        ->when($request->has("province_id"), function (Builder $query) use ($request) {
			                        $query->where("province_id", "=", $request->input("province_id"));
		                        })
		                        ->when($request->has("market_id"), function (Builder $query) use ($request) {
			                        $query->where("market_id", "=", $request->input("market_id"));
		                        })
		                        ->orderBy("name")
		                        ->get());
	}

	public function byIds(ListCitiesByIdsRequest $request) {
		return new Response(City::query()->findMany($request->input("ids")));
	}

	public function store(StoreCityRequest $request): Response {
		$city              = new City();
		$city->province_id = $request->input("province_id");
		$city->market_id   = $request->input("market_id", null);
		$city->name        = $request->input("name");
		$city->save();

		PullCityGeolocationJob::dispatch($city->getKey());

		return new Response($city);
	}

	public function update(UpdateCityRequest $request, City $city): Response {
		$city->market_id = $request->input("market_id", null);
		$city->name      = $request->input("name");
		$city->save();

		PullCityGeolocationJob::dispatch($city->getKey());

		return new Response($city);
	}

	public function destroy(DestroyCityRequest $request, City $city): Response {
		$city->delete();

		return new Response();
	}
}
