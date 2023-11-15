<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreMobileProductBracketRequest.php
 */

namespace Neo\Http\Requests\MobileProductsBrackets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreMobileProductBracketRequest extends FormRequest {
	public function rules(): array {
		return [
			"cpm"             => ["required", "numeric"],
			"budget_min"      => ["required", "numeric"],
			"budget_max"      => ["nullable", "numeric"],
			"impressions_min" => ["required", "numeric"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::mobile_products_edit->value);
	}
}
