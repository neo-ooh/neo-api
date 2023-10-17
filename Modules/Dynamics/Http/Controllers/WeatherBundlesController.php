<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundlesController.php
 */

namespace Neo\Modules\Dynamics\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Dynamics\Http\Requests\WeatherBundles\DestroyWeatherBundleRequest;
use Neo\Modules\Dynamics\Http\Requests\WeatherBundles\ListWeatherBundlesRequest;
use Neo\Modules\Dynamics\Http\Requests\WeatherBundles\MatchWeatherBundleRequest;
use Neo\Modules\Dynamics\Http\Requests\WeatherBundles\ShowWeatherBundleRequest;
use Neo\Modules\Dynamics\Http\Requests\WeatherBundles\StoreWeatherBundleRequest;
use Neo\Modules\Dynamics\Http\Requests\WeatherBundles\UpdateWeatherBundleRequest;
use Neo\Modules\Dynamics\Models\Structs\WeatherBundleTargeting;
use Neo\Modules\Dynamics\Models\WeatherBundle;

class WeatherBundlesController extends Controller {
	public function index(ListWeatherBundlesRequest $request) {
		$bundles = WeatherBundle::query()->get();

		return new Response($bundles->loadPublicRelations());
	}

	public function store(StoreWeatherBundleRequest $request) {
		$rawTargeting = $request->input("targeting", null);

		$weatherBundle                       = new WeatherBundle();
		$weatherBundle->name                 = $request->input("name");
		$weatherBundle->flight_id            = $request->input("flight_id");
		$weatherBundle->priority             = $request->input("priority");
		$weatherBundle->start_date           = $request->input("start_date");
		$weatherBundle->end_date             = $request->input("end_date");
		$weatherBundle->ignore_years         = $request->input("ignore_years");
		$weatherBundle->priority             = $request->input("priority");
		$weatherBundle->layout               = $request->input("layout");
		$weatherBundle->targeting            = $rawTargeting ? WeatherBundleTargeting::from($rawTargeting) : null;
		$weatherBundle->background_selection = $request->input("background_selection");
		$weatherBundle->save();

		$weatherBundle->formats()->attach($request->input("format_ids"));

		return new Response($weatherBundle, 201);
	}

	public function show(ShowWeatherBundleRequest $request, WeatherBundle $weatherBundle) {
		return new Response($weatherBundle->loadPublicRelations());
	}

	public function update(UpdateWeatherBundleRequest $request, WeatherBundle $weatherBundle) {
		$rawTargeting = $request->input("targeting", null);
		
		$weatherBundle->name                 = $request->input("name");
		$weatherBundle->flight_id            = $request->input("flight_id");
		$weatherBundle->priority             = $request->input("priority");
		$weatherBundle->start_date           = $request->input("start_date");
		$weatherBundle->end_date             = $request->input("end_date");
		$weatherBundle->ignore_years         = $request->input("ignore_years");
		$weatherBundle->priority             = $request->input("priority");
		$weatherBundle->layout               = $request->input("layout");
		$weatherBundle->targeting            = $rawTargeting ? WeatherBundleTargeting::from($rawTargeting) : null;
		$weatherBundle->background_selection = $request->input("background_selection");
		$weatherBundle->save();

		$weatherBundle->formats()->sync($request->input("format_ids"));

		return new Response($weatherBundle->loadPublicRelations());
	}

	public function destroy(DestroyWeatherBundleRequest $request, WeatherBundle $weatherBundle) {
		$weatherBundle->delete();

		return new Response(["status" => "ok"]);
	}

	public function match(MatchWeatherBundleRequest $request) {
		$now      = Carbon::now()->toDateString();
		$formatId = $request->input("format_id");

		WeatherBundle::query()
		             ->where(function (Builder $query) use ($now) {
			             $query->where(function (Builder $query) use ($now) {
				             $query->where("ignore_years", "=", true)
				                   ->whereBetween(DB::raw("DATE_FORMAT('$now', '%m-%d')"), [
					                   DB::raw("DATE_FORMAT(`start_date`, '%m-%d')"),
					                   DB::raw("DATE_FORMAT(`end_date`, '%m-%d')"),
				                   ]);
			             })->orWhere(function (Builder $query) use ($now) {
				             $query->where("ignore_years", "=", false)
				                   ->whereBetween($now, ['start_date', 'end_date']);
			             });

		             })
		             ->whereHas("formats", function (Builder $query) use ($formatId) {
			             $query->where("id", "=", $formatId);
		             })
			// TODO: Where Targeting
			         ->orderByDesc("priority")
		             ->first();
	}
}
