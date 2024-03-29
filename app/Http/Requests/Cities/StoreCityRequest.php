<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreCityRequest.php
 */

namespace Neo\Http\Requests\Cities;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StoreCityRequest extends FormRequest {
	public function authorize() {
		return Gate::allows(Capability::properties_edit->value);
	}

	public function rules() {
		return [
			"name"        => ["required", "string"],
			"market_id"   => ["nullable", "integer", "exists:markets,id"],
			"province_id" => ["nullable", "integer", "exists:provinces,id"],
		];
	}
}
