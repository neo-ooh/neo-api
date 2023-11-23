<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListMarketsByIdsRequest.php
 */

namespace Neo\Http\Requests\Markets;

use Illuminate\Foundation\Http\FormRequest;

class ListMarketsByIdsRequest extends FormRequest {
	public function authorize() {
		return true;
	}

	public function rules() {
		return [
			"ids" => ["array"],
		];
	}
}
