<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListAdvertisersByIdRequest.php
 */

namespace Neo\Http\Requests\Advertisers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Advertiser;
use Neo\Rules\PublicRelations;

class ListAdvertisersByIdRequest extends FormRequest {
	public function rules(): array {
		return [
			"ids"  => ["required", "array"],
			"with" => ["array", new PublicRelations(Advertiser::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::advertiser_edit->value) || Gate::allows(Capability::contracts_edit->value);
	}
}
