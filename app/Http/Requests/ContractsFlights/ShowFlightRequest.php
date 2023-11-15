<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowFlightRequest.php
 */

namespace Neo\Http\Requests\ContractsFlights;

use Illuminate\Foundation\Http\FormRequest;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Rules\PublicRelations;

class ShowFlightRequest extends FormRequest {
	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(ContractFlight::class)],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
