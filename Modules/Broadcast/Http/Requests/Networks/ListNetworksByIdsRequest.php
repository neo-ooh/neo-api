<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListNetworksByIdsRequest.php
 */

namespace Neo\Http\Requests\Networks;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class ListNetworksByIdsRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return Gate::allows(Capability::networks_edit->value)
			|| Gate::allows(Capability::campaigns_view->value)
			|| Gate::allows(Capability::properties_edit->value)
			|| Gate::allows(Capability::planner_access->value)
			|| Gate::allows(Capability::contracts_manage->value);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			"ids"  => ["required", "array"],
			"with" => ["sometimes", "array"],
		];
	}
}
