<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPlayersRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Players;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Location;

class ListPlayersRequest extends FormRequest {
	public function rules(): array {
		return [
			"location_id" => ["sometimes", "integer", new Exists(Location::class, "id")],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::networks_edit->value);
	}
}
