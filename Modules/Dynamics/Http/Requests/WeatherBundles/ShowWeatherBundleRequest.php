<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowWeatherBundleRequest.php
 */

namespace Neo\Modules\Dynamics\Http\Requests\WeatherBundles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Dynamics\Models\WeatherBundle;
use Neo\Rules\PublicRelations;

class ShowWeatherBundleRequest extends FormRequest {
	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(WeatherBundle::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::dynamics_weather_edit->value);
	}
}
