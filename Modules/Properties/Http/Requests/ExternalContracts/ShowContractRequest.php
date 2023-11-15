<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowContractRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\ExternalContracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Modules\Properties\Models\InventoryProvider;

class ShowContractRequest extends FormRequest {
	public function rules(): array {
		return [
			"inventory_id" => ["required", new Exists(InventoryProvider::class, "id")],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
