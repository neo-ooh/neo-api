<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListWeatherBackgroundsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\WeatherBackgrounds;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Models\WeatherBundleBackground;
use Neo\Enums\Capability;
use Neo\Rules\PublicRelations;

class ListWeatherBackgroundsRequest extends FormRequest {
	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(WeatherBundleBackground::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::dynamics_weather_edit->value);
	}
}
