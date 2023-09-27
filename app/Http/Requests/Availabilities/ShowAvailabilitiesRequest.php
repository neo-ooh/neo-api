<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowAvailabilitiesRequest.php
 */

namespace Neo\Http\Requests\Availabilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ShowAvailabilitiesRequest extends FormRequest {
	public function rules(): array {
		return [
			"product_ids"   => ["required", "array"],
			"product_ids.*" => ["integer"],
			"year"          => ["required", "integer"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::planner_access->value)
			|| Gate::allows(Capability::odoo_contracts->value)
			|| Gate::allows(Capability::products_edit->value);
	}
}
