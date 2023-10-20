<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MatchWeatherBundleRequest.php
 */

namespace Neo\Modules\Dynamics\Http\Requests\WeatherBundles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Properties\Models\Property;

class MatchWeatherBundleRequest extends FormRequest {
	public function rules(): array {
		return [
			"property_id" => ["required", new Exists(Property::class, "actor_id")],
			"format_id"   => ["required", new Exists(Format::class, "id")],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
