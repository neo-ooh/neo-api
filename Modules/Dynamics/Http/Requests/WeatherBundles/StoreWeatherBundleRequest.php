<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreWeatherBundleRequest.php
 */

namespace Neo\Modules\Dynamics\Http\Requests\WeatherBundles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\ContractFlight;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Dynamics\Models\Enums\WeatherBundleBackgroundSelection;
use Neo\Modules\Dynamics\Models\Enums\WeatherBundleLayout;

class StoreWeatherBundleRequest extends FormRequest {
	public function rules(): array {
		return [
			"name"     => ["required", "string"],
			"priority" => ["required", "integer"],

			"flight_id" => ["sometimes", "nullable", new Exists(ContractFlight::class)],

			"start_date"   => ["required", "date_format:Y-m-d"],
			"end_date"     => ["required", "date_format:Y-m-d"],
			"ignore_years" => ["required", "boolean"],

			"layout"               => ["required", new Enum(WeatherBundleLayout::class)],
			"background_selection" => ["required", new Enum(WeatherBundleBackgroundSelection::class)],

			"format_ids"   => ["nullable", "array"],
			"format_ids.*" => ["integer", new Exists(Format::class, "id")],
			"targeting"    => ["nullable", "array"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::dynamics_weather_edit->value);
	}
}
