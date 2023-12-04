<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListAccessTokensRequest.php
 */

namespace Neo\Http\Requests\AccessTokens;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;

class ListAccessTokensRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return Gate::allows(Capability::access_token_edit->value)
			|| Gate::allows(Capability::dynamics_weather_edit->value)
			|| Gate::allows(Capability::dynamics_news_edit->value);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			"capability" => ["sometimes", new Enum(Capability::class)],
		];
	}
}
