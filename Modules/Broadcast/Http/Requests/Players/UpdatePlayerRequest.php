<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdatePlayerRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Players;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdatePlayerRequest extends FormRequest {
	public function rules(): array {
		return [
			"dynamics_debug" => ["required", "boolean"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::networks_edit->value);
	}
}
