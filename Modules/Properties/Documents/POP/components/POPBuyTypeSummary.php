<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPBuyTypeSummary.php
 */

namespace Neo\Modules\Properties\Documents\POP\components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Properties\Documents\POP\POPFlight;
use Neo\Modules\Properties\Documents\POP\POPFlightNetwork;

class POPBuyTypeSummary extends Component {
	/**
	 * @param Collection<POPFlight> $flights
	 */
	public function __construct(protected Collection $flights) {

	}

	public function render() {

		$allNetworks = collect();

		/** @var POPFlight $flight */
		foreach ($this->flights as $flight) {

			$networkLines = collect($flight->lines)
				->groupBy("product.property.network_id");

			/** @var POPFlightNetwork $flightNetwork */
			foreach ($flight->networks as $flightNetwork) {
				$lines = $networkLines[$flightNetwork->network_id];

				/** @var Network $network */
				$network = $lines[0]->product->property->network;

				$allNetworks[] = [
					"network_id"             => $network->id,
					"name"                   => $network->name,
					"color"                  => "#" . $network->toned_down_color,
					"start_date"             => $flight->start_date,
					"end_date"               => $flight->end_date,
					"contracted_impressions" => $flightNetwork->getContractedImpressions(),
					"counted_impressions"    => $flightNetwork->getDeliveredImpressions(),
					"media_value"            => $flightNetwork->getContractedMediaValue() * $flightNetwork->getDeliveredPercent(),
					"net_investment"         => $flightNetwork->getContractedNetInvestment(),
				];
			}
		}

		$networks = $allNetworks->groupBy("network_id")->map(fn(Collection $networks) => ([
			"network_id"             => $networks[0]["network_id"],
			"name"                   => $networks[0]["name"],
			"color"                  => $networks[0]["color"],
			"start_date"             => $networks->min("start_date"),
			"end_date"               => $networks->max("end_date"),
			"contracted_impressions" => $networks->sum("contracted_impressions"),
			"counted_impressions"    => $networks->sum("counted_impressions"),
			"media_value"            => $networks->sum("media_value"),
			"net_investment"         => $networks->sum("net_investment"),
		]));

		$totals = [
			"start_date"             => $networks->min("start_date"),
			"end_date"               => $networks->min("end_date"),
			"contracted_impressions" => $networks->sum("contracted_impressions"),
			"counted_impressions"    => $networks->sum("counted_impressions"),
			"media_value"            => $networks->sum("media_value"),
			"net_investment"         => $networks->sum("net_investment"),
		];

		return view('properties::pop.flight-summary', [
			"title"    => __("pop.flight-type-" . $this->flights[0]->flight_type->value),
			"subtitle" => "",
			"networks" => $networks,
			"totals"   => $totals,
		])->render();
	}
}
