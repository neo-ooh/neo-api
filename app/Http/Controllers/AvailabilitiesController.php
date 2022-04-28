<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AvailabilitiesController.php
 */

namespace Neo\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Neo\Enums\ProductsFillStrategy;
use Neo\Http\Requests\ListAvailabilitiesRequest;
use Neo\Models\ContractFlight;
use Neo\Models\Product;

class AvailabilitiesController {
    public function index(ListAvailabilitiesRequest $request) {
        $productIds   = $request->input("product_ids");
        $productSpots = $request->input("product_spots");

        $arraySizeValidator = Validator::make([
            "product_ids_count"   => count($productIds),
            "product_spots_count" => count($productSpots),
        ], [
            "product_spots_count" => ["same:product_ids_count"]
        ]);

        if ($arraySizeValidator->fails()) {
            throw new ValidationException($arraySizeValidator);
        }

        $from = Carbon::parse($request->input("from"));
        $to   = Carbon::parse($request->input("to"));

        // Pull all reservations made for the specified product that intersect with the provided interval
        $reservations = DB::table('products')
                          ->select('products.id', 'contracts_lines.spots', 'contracts_flights.start_date', 'contracts_flights.end_date')
                          ->join('contracts_lines', 'contracts_lines.product_id', '=', 'products.id')
                          ->join('contracts_flights', 'contracts_lines.flight_id', '=', 'contracts_flights.id')
                          ->whereIn("products.id", $productIds)
                          ->where('contracts_flights.start_date', '<', $to->toDateString())
                          ->where('contracts_flights.end_date', '>', $from->toDateString())
                          ->where('contracts_flights.type', '<>', ContractFlight::BUA)
                          ->get()
                          ->map(function ($reservation) {
                              return [
                                  "product_id" => $reservation->id,
                                  "spots"      => $reservation->spots,
                                  "from"       => Carbon::parse($reservation->start_date),
                                  "to"         => Carbon::parse($reservation->end_date),
                              ];
                          });

        // Prepare a dates array that will be used as a base for the response
        $datesList  = collect();
        $dateCursor = $from;
        $boundary   = $to->clone()->addDay();
        do {
            $datesList[] = $dateCursor->clone();
            $dateCursor->addDay();
        } while ($dateCursor->isBefore($boundary));

        // Pull all products as we will need informations about them
        $products = Product::query()->with("category")->findMany($productIds);

        $availabilities = [];

        // Loop accross all products
        foreach ($productIds as $i => $productId) {
            /** @var Product|null $product */
            $product = $products->firstWhere("id", "=", $productId);

            // Ignore if product Model is missing
            if (!$product) {
                continue;
            }

            $spots = $productSpots[$i];

            // Get the reservations of the product
            $productReservations = $reservations->filter(fn($reservation) => $reservation["product_id"] === $product->id);

            // If the product has no reservations attached, bypass the loop and mark it as fully available
            if ($productReservations->count() === 0) {
                $availabilities[] = [
                    "product_id"     => $product->getKey(),
                    "availabilities" => "full",
                ];

                continue;
            }

            // Build the availability array for each date
            $dates = collect();
            foreach ($datesList as $date) {
                $dateReservations = $productReservations->filter(function ($reservation) use ($date) {
                    return $date->gte($reservation["from"]) && $date->lte($reservation["to"]);
                });

                // Determine how many time the product can be booked at the same time
                // We have to do this in the loop as digital products available number of spots may change from one day to another
                $spotsCounts = $product->category->fill_strategy === ProductsFillStrategy::static ? $product->quantity : $product->spots_count;

                $reservedSpots = $dateReservations->sum("spots");

                $dates[] = [
                    "date"             => $date->toDateString(),
                    "available"        => $reservedSpots < $spotsCounts - $spots,
                    "max_reservations" => $spotsCounts,
                    "reserved"         => $reservedSpots
                ];
            }

            $state = 'none';
            if ($dates->every("available", "=", true)) {
                $state = 'full';
            } else if ($dates->some("available", "=", true)) {
                $state = 'partial';
            }

            $availabilities[] = [
                "product_id"     => $product->getKey(),
                "availabilities" => $state,
                "dates"          => $dates,
            ];
        }

        return new Response($availabilities);
    }
}
