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
use Neo\Modules\Properties\Models\PropertyNetwork;
use Neo\Rules\PublicRelations;

class ListPropertiesNetworksRequest extends FormRequest {
	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(PropertyNetwork::class)],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
