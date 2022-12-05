<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFlightsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ContractsFlights\ShowFlightRequest;
use Neo\Models\ContractFlight;

class ContractFlightsController extends Controller {
    public function show(ShowFlightRequest $request, ContractFlight $flight) {
        return new Response($flight->loadPublicRelations());
    }
}
