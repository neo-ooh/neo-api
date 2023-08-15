<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListLocationsByIdsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Rules\PublicRelations;

class ListLocationsByIdsRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool {
		// Users are allowed to list locations. Anyway, they only get the ones they can access.
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array {
		return [
			"ids"  => ["required", "array"],
			"with" => ["sometimes", "array", new PublicRelations(Location::class)],
		];
	}
}
