<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListNewsRecordsRequest.php
 */

namespace Neo\Modules\Dynamics\Http\Requests\NewsRecords;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListNewsRecordsRequest extends FormRequest {
	public function rules(): array {
		return [
			"categories" => ["required", "array"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::dynamics_news_edit->value) || Gate::allows(Capability::dynamics_news_pull->value);
	}
}
