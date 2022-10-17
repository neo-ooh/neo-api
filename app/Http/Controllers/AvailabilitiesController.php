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
use Illuminate\Database\Eloquent\Collection;
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
        $productIds    = $request->input("product_ids");
        $productsSpots = $request->input("product_spots");

        $arraySizeValidator = Validator::make([
            "product_ids_count"   => count($productIds),
            "product_spots_count" => count($productsSpots),
        ], [
            "product_spots_count" => ["same:product_ids_count"],
        ]);

        if ($arraySizeValidator->fails()) {
            throw new ValidationException($arraySizeValidator);
        }

        $from = Carbon::parse($request->input("from"));
        $to   = Carbon::parse($request->input("to"));

        $productIdsChunks = collect($productIds)->chunk(500);

        // Pull all the products
        $products = new Collection();

        foreach ($productIdsChunks as $chunk) {
            // Pull all products as we will need information about them
            $products = $products->merge(Product::query()
                                                ->with(["loop_configurations", "category.loop_configurations"])
                                                ->findMany($chunk));
        }


        // Pull all reservations made for the specified product or their linked ones and who intersect with the provided interval
        $reservations = collect();

        $allProductsIds       = $products->pluck("id")->merge($products->pluck("linked_product_id")->whereNotNull())->unique();
        $allProductsIdsChunks = $allProductsIds->chunk(500);

        foreach ($allProductsIdsChunks as $chunk) {
            $reservations = $reservations->merge(DB::table('products')
                                                   ->select('products.id', 'contracts_lines.spots', 'contracts_flights.start_date', 'contracts_flights.end_date')
                                                   ->join('contracts_lines', 'contracts_lines.product_id', '=', 'products.id')
                                                   ->join('contracts_flights', 'contracts_lines.flight_id', '=', 'contracts_flights.id')
                                                   ->whereIn("products.id", $chunk)
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
                                                   }));
        }

        // Prepare a dates array that will be used as a base for the response
        $datesList  = collect();
        $dateCursor = $from;
        $boundary   = $to->clone()->addDay();
        do {
            $datesList[] = $dateCursor->clone();
            $dateCursor->addDay();
        } while ($dateCursor->isBefore($boundary));

        $availabilities = [];

        // Loop accross all products
        foreach ($productIds as $i => $productId) {
            /** @var Product|null $product */
            $product = $products->firstWhere("id", "=", $productId);

            // Ignore if product OdooModel is missing
            if (!$product) {
                continue;
            }

            $spots = $productsSpots[$i];

            // Get the reservations of the product or its linked counterpart
            $productReservations = $reservations->filter(fn($reservation) => $reservation["product_id"] === $product->id || $reservation["product_id"] === $product->linked_product_id);

            // Build the availability array for each date
            $dates = collect();
            foreach ($datesList as $date) {
                // Determine how many time the product can be booked at the same time
                if ($product->category->fill_strategy === ProductsFillStrategy::digital) {
                    // Get the loop configuration for the current date
                    $loopConfiguration = $product->getLoopConfiguration($date);

                    // If the loop configuration is missing, we cannot determine the availability of the product
                    if (!$loopConfiguration) {
                        $dates[] = [
                            "date"      => $date->toDateString(),
                            "available" => "unknown",
                        ];
                        continue;
                    }

                    $productSpots = $loopConfiguration->free_spots_count;
                } else {
                    $productSpots = $product->quantity;
                }

                $dateReservations = $productReservations->filter(function ($reservation) use ($date) {
                    return $date->gte($reservation["from"]) && $date->lte($reservation["to"]);
                });

                $reservedSpots = $dateReservations->sum("spots");

                $dates[] = [
                    "date"             => $date->toDateString(),
                    "available"        => $reservedSpots <= $productSpots - $spots,
                    "max_reservations" => $productSpots,
                    "reserved"         => $reservedSpots,
                ];
            }

            $state = 'none';
            if ($dates->every("available", "===", true)) {
                $state = 'full';
            } else if ($dates->some("available", "===", true)) {
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
