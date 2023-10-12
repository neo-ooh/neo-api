<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreRecordsRequest.php
 */

namespace Neo\Modules\Dynamics\Http\Requests\PlayRecords;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Modules\Broadcast\Models\Player;

class StoreRecordsRequest extends FormRequest {
	public function rules(): array {
		return [
			"player_id" => ["required", "integer", new Exists(Player::class, "id")],
			"records"   => ["required", "array"],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
