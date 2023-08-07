<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPSummaryTotals.php
 */

namespace Neo\Modules\Properties\Documents\POP\components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Neo\Modules\Properties\Documents\POP\POPFlight;
use Neo\Modules\Properties\Documents\POP\POPFlightNetwork;
use Neo\Modules\Properties\Documents\POP\POPRequest;

class POPSummaryTotals extends Component {
	public function __construct(protected POPRequest $request) {

	}

	public function render() {
		$flights = collect();

		/** @var POPFlight $flight */
		foreach ($this->request->flights as $flight) {
			/** @var Collection $networkValues */
			$networkValues = $flight->networks->toCollection()->map(function (POPFlightNetwork $network) {
				return [
					"contracted_impressions"    => $network->getContractedImpressions(),
					"contracted_media_value"    => $network->getContractedMediaValue(),
					"contracted_net_investment" => $network->getContractedNetInvestment(),
					"counted_impressions"       => $network->getDeliveredImpressions(),
					"media_value"               => $network->getContractedMediaValue() * $network->getDeliveredPercent(),
					"net_investment"            => $network->getContractedNetInvestment() * $network->getDeliveredPercent(),
				];
			});

			$flights[] = [
				"name"                      => $flight->flight_name,
				"type"                      => $flight->flight_type,
				"start_date"                => $flight->start_date,
				"end_date"                  => $flight->end_date,
				"contracted_impressions"    => $networkValues->sum("contracted_impressions"),
				"contracted_media_value"    => $networkValues->sum("contracted_media_value"),
				"contracted_net_investment" => $networkValues->sum("contracted_net_investment"),
				"counted_impressions"       => $networkValues->sum("counted_impressions"),
				"media_value"               => $networkValues->sum("media_value"),
				"net_investment"            => $networkValues->sum("net_investment"),
			];
		}

		if ($this->request->summary_breakdown === 'buy-types') {
			$flights = $flights->groupBy("type.value")->map(fn(Collection $flights) => ([
				"name"                      => __("pop.flight-type-" . $flights[0]["type"]->value),
				"type"                      => $flights[0]["type"],
				"start_date"                => $flights->min("start_date"),
				"end_Date"                  => $flights->min("end_date"),
				"contracted_impressions"    => $flights->sum("contracted_impressions"),
				"contracted_media_value"    => $flights->sum("contracted_media_value"),
				"contracted_net_investment" => $flights->sum("contracted_net_investment"),
				"counted_impressions"       => $flights->sum("counted_impressions"),
				"media_value"               => $flights->sum("media_value"),
				"net_investment"            => $flights->sum("net_investment"),
			]));
		}

		$contractedImpressions   = $flights->sum("contracted_impressions");
		$contractedNetInvestment = $flights->sum("contracted_net_investment");

		$contractedValues = [
			"media_value"    => $flights->sum("contracted_media_value"),
			"impressions"    => $contractedImpressions,
			"net_investment" => $contractedNetInvestment,
			"cpm"            => $contractedNetInvestment / $contractedImpressions * 1000,
		];

		$totalImpressions = $flights->sum("counted_impressions");
		$totalInvestment  = $flights->sum("contracted_net_investment");

		$totals = [
			"media_value"         => $flights->sum("media_value"),
			"counted_impressions" => $totalImpressions,
			"net_investment"      => $totalInvestment,
			"cpm"                 => $totalInvestment / $totalImpressions * 1000,
		];

		return view('properties::pop.summary-totals', [
			"contracted" => $contractedValues,
			"flights"    => $flights,
			"totals"     => $totals,
		])->render();
	}
}
