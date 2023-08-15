<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPPReface.php
 */

namespace Neo\Modules\Properties\Documents\POP\components;

use Illuminate\Support\Carbon;
use Illuminate\View\Component;
use Neo\Modules\Properties\Documents\POP\POPFlight;
use Neo\Modules\Properties\Documents\POP\POPRequest;
use Neo\Resources\Contracts\FlightType;

class POPPReface extends Component {
	public function __construct(protected POPRequest $request) {
	}

	public function render() {
		$flights = $this->request->flights->toCollection()
		                                  ->where("flight_type", '!==', FlightType::BUA);

		if ($flights->count() === 0) {
			$flights = $this->request->flights->toCollection();
		}

		$flightsDates = $flights->map(fn(POPFlight $flight) => ([
			"start_date" => Carbon::parse($flight->start_date),
			"end_date"   => Carbon::parse($flight->end_date),
		]));

		return view('properties::pop.preface', [
			"contract_name"   => $this->request->contract_number,
			"advertiser_name" => $this->request->advertiser,
			"client_name"     => $this->request->client,
			"start_date"      => $flightsDates->min("start_date"),
			"end_date"        => $flightsDates->max("end_date"),
			"salesperson"     => $this->request->salesperson,
			"presented_to"    => $this->request->presented_to,
		])->render();
	}
}
