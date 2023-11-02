<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundleBackgroundsController.php
 */

namespace Neo\Modules\Dynamics\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Dynamics\Models\WeatherBundle;
use Neo\Modules\Dynamics\Models\WeatherBundleBackground;
use Neo\Modules\Properties\Http\Requests\WeatherBackgrounds\DestroyWeatherBackgroundRequest;
use Neo\Modules\Properties\Http\Requests\WeatherBackgrounds\ListWeatherBackgroundsRequest;
use Neo\Modules\Properties\Http\Requests\WeatherBackgrounds\ShowWeatherBackgroundRequest;
use Neo\Modules\Properties\Http\Requests\WeatherBackgrounds\StoreWeatherBackgroundRequest;

class WeatherBundleBackgroundsController extends Controller {
	public function index(ListWeatherBackgroundsRequest $request, WeatherBundle $weatherBundle) {
		$backgrounds = $weatherBundle->backgrounds()->get();

		return new Response($backgrounds->loadPublicRelations());
	}

	/**
	 * @throws UnsupportedFileFormatException
	 */
	public function store(StoreWeatherBackgroundRequest $request, WeatherBundle $weatherBundle) {
		$file = $request->file("background");

		$background            = new WeatherBundleBackground();
		$background->bundle_id = $weatherBundle->getKey();
		$background->format_id = $request->input("format_id");
		$background->extension = $file->extension();

		$background->weather = $request->input("weather");
		$background->period  = $request->input("period");

		try {
			DB::beginTransaction();

			$background->save();
			$background->store($request->file("background"));

			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();
			throw $e;
		}

		return new Response($background, 201);
	}

	public function show(ShowWeatherBackgroundRequest $request, WeatherBundle $weatherBundle, WeatherBundleBackground $weatherBackground) {
		return new Response($weatherBackground->loadPublicRelations());
	}

	public function destroy(DestroyWeatherBackgroundRequest $request, WeatherBundle $weatherBundle, WeatherBundleBackground $weatherBackground) {
		$weatherBackground->delete();

		return new Response(["status" => "ok"]);
	}
}
