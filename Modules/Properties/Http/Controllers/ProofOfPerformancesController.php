<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProofOfPerformancesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Mpdf\MpdfException;
use Neo\Documents\Exceptions\UnknownGenerationException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Documents\POP\PDFPOP;
use Neo\Modules\Properties\Documents\POP\POPFlight;
use Neo\Modules\Properties\Documents\POP\POPFlightGroup;
use Neo\Modules\Properties\Documents\POP\POPFlightNetwork;
use Neo\Modules\Properties\Documents\POP\POPRequest;
use Neo\Modules\Properties\Documents\POP\POPScreenshot;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Http\Requests\ProofOfPerformances\BuildPOPRequest;
use Neo\Modules\Properties\Http\Requests\ProofOfPerformances\GetPOPBaseRequest;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Properties\Models\ContractLine;
use Neo\Resources\FlightType;

class ProofOfPerformancesController extends Controller {
    public function getBase(GetPOPBaseRequest $request, Contract $contract) {
        // Build the base request for building a Proof of Performance file
        $contract->load(["flights.lines.product.property", "flights.lines.product.category"]);
        $flights = POPFlight::collection([]);

        $contractFlights = $contract->flights->sortBy(function (ContractFlight $flight) {
            return [
                       FlightType::Guaranteed->value => 1,
                       FlightType::Bonus->value      => 2,
                       FlightType::BUA->value        => 3,
                   ][$flight->type->value];
        });

        foreach ($contractFlights as $flight) {
            $flight->append("performances");
            $flight->fillLinesPerformances();

            $flightLines = $flight->lines->where("product.category.type", "=", ProductType::Digital);
            if ($flightLines->isEmpty()) {
                continue;
            }

            $flights[] = new POPFlight(
                include    : true,
                flight_id  : $flight->getKey(),
                flight_name: $flight->name ?? "Flight",
                flight_type: $flight->type,
                start_date : $flight->start_date->toDateString(),
                end_date   : $flight->end_date->toDateString(),
                networks   : POPFlightNetwork::collection($flightLines->groupBy("product.property.network_id")
                                                                      ->map(/**
                                                                       * @param Collection $lines
                                                                       * @return POPFlightNetwork
                                                                       */ function (Collection $lines) use ($flight) {
                                                                          /** @var ContractLine $line */
                                                                          $line           = $lines[0];
                                                                          $networkId      = $line->product->property->network_id;
                                                                          $deliveryRatios = $lines->map(fn(ContractLine $line) => $line->impressions > 0 ? ($line->performances->impressions ?? 0) / $line->impressions : 0);

                                                                          return new POPFlightNetwork(
                                                                              network_id                      : $networkId,
                                                                              contracted_impressions          : $lines->sum("impressions"),
                                                                              contracted_impressions_factor   : 1,
                                                                              contracted_media_value          : round($lines->sum("media_value")),
                                                                              contracted_media_value_factor   : 1,
                                                                              contracted_net_investment       : round($lines->sum("price")),
                                                                              contracted_net_investment_factor: 1,
                                                                              delivered_impressions           : $flight->performances->where("network_id", "=", $networkId)
                                                                                                                                     ->sum("impressions"),
                                                                              max_delivered_ratio             : $deliveryRatios->max(),
                                                                              min_delivered_ratio             : $deliveryRatios->min(),
                                                                              delivered_impressions_factor    : 1.5,
                                                                          );
                                                                      })->values()),
                breakdown  : "products",
                groups     : POPFlightGroup::collection([]),
                screenshots: POPScreenshot::collection([]),
                lines      : $flightLines->all(),
            );
        }

        return new Response(new POPRequest(
                                contract_id      : $contract->getKey(),
                                contract_number  : $contract->contract_id,
                                salesperson      : $contract->salesperson->name,
                                client           : trim(explode(',', $contract->client?->name ?? '')[0]),
                                presented_to     : trim(explode(',', $contract->client?->name ?? '')[1] ?? '') ?? "",
                                advertiser       : $contract->advertiser?->name ?? '',
                                locale           : App::getLocale(),
                                summary_breakdown: 'flights',
                                flights          : $flights,
                            ));
    }

    /**
     * @throws UnknownGenerationException
     * @throws MpdfException
     */
    public function build(BuildPOPRequest $request, Contract $contract) {
        $popData = POPRequest::from($request->input("pop"));

        // First, we want to fill in the line for each flight/network
        $contract->load("flights.lines.product.property.address.city.province", "flights.lines.product.category");
        $contract->flights->append("products_performances");

        /** @var POPFlight $flight */
        foreach ($popData->flights as $flight) {
            if (!$flight->include) {
                continue;
            }

            /** @var ContractFlight $contractFlight */
            $contractFlight = $contract
                ->flights
                ->firstWhere("id", "=", $flight->flight_id);

            if (!$contractFlight) {
                continue;
            }

            $contractFlight->fillLinesPerformances();
            $flightLines = $contractFlight->lines->where("product.category.type", "=", ProductType::Digital);

            if (!$flightLines || $flightLines->isEmpty()) {
                continue;
            }

            /** @var POPFlightNetwork $network */
            $flight->lines = $flightLines->all();
            $flight->applyDeliveryRatioToLines();
        }

        $pop = PDFPOP::make($popData);
        $pop->build();

        return $pop->asResponse();
    }
}
