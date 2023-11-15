<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\ExternalContracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\InventoryProvider;

class ImportContractRequest extends FormRequest {
	public function rules(): array {
		return [
			"inventory_id" => ["required", new Exists(InventoryProvider::class, "id")],
			"contract_id"  => ["required", "string"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::contracts_edit->value);
	}
}
