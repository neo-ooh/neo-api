<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListScreenshotsRequest.php
 */

namespace Neo\Http\Requests\Screenshots;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Properties\Models\Screenshot;
use Neo\Rules\PublicRelations;

class ListScreenshotsRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool {
		return Gate::allows(Capability::contracts_edit->value);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array {
		return [
			"flight_id" => ["required", "integer", new Exists(ContractFlight::class, "id")],

			"page"  => ["integer"],
			"count" => ["integer"],

			"with" => ["sometimes", "array", new PublicRelations(Screenshot::class)],
		];
	}
}
