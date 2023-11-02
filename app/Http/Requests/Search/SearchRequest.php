<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchRequest.php
 */

namespace Neo\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest {
	public function rules(): array {
		return [
			"resources"   => ["required", "array"],
			"resources.*" => ["string", Rule::in(["actors", "campaigns", "libraries"])],
			"query"       => ["required", "string", "min:3"],

			"hierarchy" => ["sometimes", "boolean"],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
