<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListMobileProductsRequest.php
 */

namespace Neo\Http\Requests\MobileProducts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\MobileProduct;
use Neo\Rules\PublicRelations;

class ListMobileProductsRequest extends FormRequest {
	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(MobileProduct::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::mobile_products_edit->value);
	}
}
