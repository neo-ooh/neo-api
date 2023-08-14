<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListContractsRequest.php
 */

namespace Neo\Http\Requests\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Contract;
use Neo\Rules\PublicRelations;

class ListContractsRequest extends FormRequest {
	public function rules(): array {
		return [
			"actor_id" => ["sometimes", "exists:actors,id"],

			"with" => ["array", new PublicRelations(Contract::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::contracts_edit->value);
	}
}
