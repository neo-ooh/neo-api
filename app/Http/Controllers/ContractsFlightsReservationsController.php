<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsFlightsReservationsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ContractsFlights\SyncContractFlightReservationsRequest;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Properties\Models\ContractReservation;

class ContractsFlightsReservationsController {
	public function sync(SyncContractFlightReservationsRequest $request, ContractFlight $flight) {
		$flight->reservations()
		       ->whereNotIn("id", $request->input("reservations", []))
		       ->update(["flight_id" => null]);
		ContractReservation::query()
		                   ->whereIn("id", $request->input("reservations", []))
		                   ->update(["flight_id" => $flight->getKey()]);

		return new Response($flight->reservations);
	}
}
