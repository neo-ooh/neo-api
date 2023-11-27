<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IdentifyPlayerRequest.php
 */

namespace Neo\Modules\Dynamics\Http\Requests\Identify;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class IdentifyPlayerRequest extends FormRequest {
	public function rules(): array {
		return [
			"player_type" => ["required", "string"],
			"player_id"   => ["required", "string"],

			"width"  => ["required", "integer"],
			"height" => ["required", "integer"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::dynamics_weather_pull->value)
			|| Gate::allows(Capability::dynamics_news_pull->value);
	}
}
