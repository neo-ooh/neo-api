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

			$deliveredImpressions = $flightNetwork->delivered_impressions * $flightNetwork->delivered_impressions_factor;
			$deliveryPercent      = $deliveredImpressions / $flightNetwork->contracted_impressions;

			$networks[] = [
				"name"                   => $network->name,
				"color"                  => "#" . $network->color,
				"start_date"             => $this->flight->start_date,
				"end_date"               => $this->flight->end_date,
				"contracted_impressions" => $flightNetwork->contracted_impressions,
				"counted_impressions"    => $deliveredImpressions,
				"media_value"            => $flightNetwork->contracted_media_value * $deliveryPercent,
				"net_investment"         => $flightNetwork->contracted_net_investment * $deliveryPercent,
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
			"flight"   => $this->flight,
			"networks" => $networks,
			"totals"   => $totals,
		])->render();
	}
}
