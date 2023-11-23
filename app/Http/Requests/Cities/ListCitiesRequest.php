<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListCitiesRequest.php
 */

namespace Neo\Http\Requests\Cities;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\Market;
use Neo\Models\Province;

class ListCitiesRequest extends FormRequest {
	public function authorize() {
		return Gate::allows(Capability::properties_edit->value);
	}

	public function rules() {
		return [
			"province_id" => ["sometimes", new Exists(Province::class, "id")],
			"market_id"   => ["sometimes", new Exists(Market::class, "id")],
		];
	}
}
