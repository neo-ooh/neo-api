<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncContractFlightReservationsRequest.php
 */

namespace Neo\Http\Requests\ContractsFlights;

use Auth;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\ContractFlight;

class SyncContractFlightReservationsRequest extends FormRequest {
	public function rules(): array {
		return [
			"reservations"   => ["nullable", "array"],
			"reservations.*" => ["integer", "exists:contracts_reservations,id"],
		];
	}

	public function authorize(): bool {
		$flightId = $this->route()->originalParameter("flight");
		/** @var ContractFlight $flight */
		$flight = ContractFlight::query()->findOrFail($flightId)->load("contract");
		return ($flight->contract->salesperson_id === Auth::id() && Gate::allows(Capability::contracts_edit->value))
			|| Gate::allows(Capability::contracts_manage->value);
	}
}
