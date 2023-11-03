<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPropertiesNetworksRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PropertiesNetworks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\PropertyNetwork;
use Neo\Rules\PublicRelations;

class StorePropertyNetworkRequest extends FormRequest {
	public function rules(): array {
		return [
			"name" => ["required", "string"],
			"slug" => ["required", "string"],
			"color" => ["required", "string"],
			"ooh_sales" => ["required", "boolean"],
			"mobile_sales" => ["required", "boolean"],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::properties_edit->value);
	}
}
