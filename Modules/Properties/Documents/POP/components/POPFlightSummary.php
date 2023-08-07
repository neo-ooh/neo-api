<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPFlightSummary.php
 */

namespace Neo\Modules\Properties\Documents\POP\components;

use Illuminate\View\Component;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Properties\Documents\POP\POPFlight;
use Neo\Modules\Properties\Documents\POP\POPFlightNetwork;

class POPFlightSummary extends Component {
	public function __construct(protected POPFlight $flight) {

	}

	public function render() {
		$networkLines = collect($this->flight->lines)
			->groupBy("product.property.network_id");

		$networks = collect();

		/** @var POPFlightNetwork $flightNetwork */
		foreach ($this->flight->networks as $flightNetwork) {
			$lines = $networkLines[$flightNetwork->network_id];

			/** @var Network $network */
			$network = $lines[0]->product->property->network;

			$networks[] = [
				"name"                   => $network->name,
				"color"                  => "#" . $network->toned_down_color,
				"start_date"             => $this->flight->start_date,
				"end_date"               => $this->flight->end_date,
				"contracted_impressions" => $flightNetwork->getContractedImpressions(),
				"counted_impressions"    => $flightNetwork->getDeliveredImpressions(),
				"media_value"            => $flightNetwork->getContractedMediaValue() * $flightNetwork->getDeliveredPercent(),
				"net_investment"         => $flightNetwork->getContractedNetInvestment() * $flightNetwork->getDeliveredPercent(),
			];
		}

		$totals = [
			"start_date"             => $this->flight->start_date,
			"end_date"               => $this->flight->end_date,
			"contracted_impressions" => $networks->sum("contracted_impressions"),
			"counted_impressions"    => $networks->sum("counted_impressions"),
			"media_value"            => $networks->sum("media_value"),
			"net_investment"         => $networks->sum("net_investment"),
		];

		return view('properties::pop.flight-summary', [
			"title"    => $this->flight->flight_name,
			"subtitle" => __("pop.flight-type-" . $this->flight->flight_type->value),
			"networks" => $networks,
			"totals"   => $totals,
		])->render();
	}
}
