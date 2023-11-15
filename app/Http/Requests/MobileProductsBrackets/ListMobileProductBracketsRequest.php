<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListMobileProductBracketsRequest.php
 */

namespace Neo\Http\Requests\MobileProductsBrackets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\MobileProductBracket;
use Neo\Rules\PublicRelations;

class ListMobileProductBracketsRequest extends FormRequest {
	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(MobileProductBracket::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::mobile_products_edit->value);
	}
}
