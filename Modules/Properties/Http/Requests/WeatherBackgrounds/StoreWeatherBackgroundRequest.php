<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreWeatherBackgroundRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\WeatherBackgrounds;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Format;

class StoreWeatherBackgroundRequest extends FormRequest {
	public function rules(): array {
		return [
			"format_id" => ["required", new Exists(Format::class, "id")],

			"weather" => ["nullable", "string"],
			"period"  => ["nullable", "string"],

			"background" => ["required", "image"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::dynamics_weather_edit->value);
	}
}
