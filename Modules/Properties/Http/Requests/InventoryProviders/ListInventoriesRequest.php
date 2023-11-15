<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListInventoriesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryProviders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Services\InventoryCapability;

class ListInventoriesRequest extends FormRequest {
	public function rules(): array {
		return [
			"capabilities"   => ["sometimes", "array"],
			"capabilities.*" => [new Enum(InventoryCapability::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::inventories_edit->value)
			|| Gate::allows(Capability::properties_inventories_view->value);
	}
}
