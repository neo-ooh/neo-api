<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImpressionsController.php
 */

namespace Neo\Http\Controllers;

use Carbon\Carbon;
use Neo\Documents\XLSX\Worksheet;
use Neo\Exceptions\InvalidRequestException;
use Neo\Http\Requests\Impressions\ExportBroadsignImpressionsRequest;
use Neo\Models\Location;
use Neo\Models\OpeningHours;
use Neo\Models\Product;
use Neo\Models\Property;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Models\LoopPolicy;
use Neo\Services\Broadcast\BroadSign\Models\Skin;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ImpressionsController {
    public function broadsignDisplayUnit(ExportBroadsignImpressionsRequest $request, int $displayUnitId) {
        /** @var Location|null $location */
        $location = Location::query()->where("external_id", "=", $displayUnitId)->first();

        if (!$location) {
            throw new InvalidRequestException("The provided Display Unit Id is not registered on Connect.");
        }

        $config = Broadcast::network($location->network_id)->getConfig();

        if (!($config instanceof BroadSignConfig)) {
            throw new InvalidRequestException("The provided Display Unit Id is not a BroadSign Display Unit.");
        }

        $client = new BroadsignClient($config);

        // We need to generate a file for each week of the year, for each frame of the display unit
        // Load the property, impressions data and traffic data attached with this location
        /** @var Product|null $product */
        $product = $location->products()
                            ->with(["impressions_models", "category.impressions_models"])
                            ->withCount("locations")
                            ->first();

        if (!$product) {
            throw new InvalidRequestException("The Display Unit is not associated with a product.");
        }

        /** @var Property $property */
        $property                         = Property::query()
                                                    ->with(["opening_hours", "traffic.weekly_data"])
                                                    ->find($product->property_id);
        $property->rolling_weekly_traffic = $property->traffic->getRollingWeeklyTraffic();

        // Load all the frames, of the display unit, and load their loop policies as well
        $frames = Skin::byDisplayUnit($client, ["display_unit_id" => $location->external_id]);
        $frames->each(/**
         * @param Skin $frame
         */ function ($frame) use ($client) {
            $frame->loop_policy = LoopPolicy::get($client, $frame->loop_policy_id);
        });

        // Create a spreadsheet document
        $doc   = new Spreadsheet();
        $sheet = new Worksheet(null, 'Worksheet 1');
        $doc->addSheet($sheet);
        $doc->removeSheetByIndex(0);

        // Insert the headers
        $sheet->printRow([
            "Display Unit Id",
            "Frame Id",
            "Start Date",
            "End Date",
            "Start Time",
            "End Time",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday",
            "Total Impressions per hour",
        ]);

        // And now, for each frame of the display unit, for every day of the week, we have to calculate the hourly impressions.
        $datePointer = Carbon::now()->startOf("week");
        $endBoundary = $datePointer->clone()->addYear();

        // Dump a failover row for every frame
        foreach ($frames as $frame) {
            $sheet->printRow([
                $location->external_id,
                $frame->id,
                $datePointer->toDateString(),
                $endBoundary->toDateString(),
                "00:00:00",
                "23:59:00",
                1,
                1,
                1,
                1,
                1,
                1,
                1,
                1
            ]);
        }

        $openLengths = $property->opening_hours->mapWithKeys(/**
         * @param OpeningHours $hours
         * @return array
         */ fn($hours) => [$hours->weekday => $hours->open_at->diffInMinutes($hours->close_at, true)]);

        // For each week
        do {
            // Get the week traffic
            $traffic = floor($property->rolling_weekly_traffic[(int)strftime("%W", $datePointer->timestamp)] / 7);

            // For each day of the week
            for ($i = 0; $i < 7; $i++) {
                $weekday = $i + 1;
                $date    = $datePointer->clone()->addDays($i);
                // Get the appropriate impressions model
                $model = $product->getImpressionModel($date);

                if (!$model) {
                    // No model, no impressions
                    continue;
                }

                $el                = new ExpressionLanguage();
                $impressionsPerDay = $el->evaluate($model->formula, array_merge([
                    "traffic" => $traffic,
                    "faces"   => $product->quantity,
                    "spots"   => 1,
                ],
                    $model->variables
                ));

                // Because the impression for the product is spread on all the display unit attached to it,
                // we divide the number of impressions by the number of display unit for the product
                $impressionsPerDay = $impressionsPerDay / $product->locations_count;

                /** @var OpeningHours $hours */
                $hours = $property->opening_hours->firstWhere("weekday", "=", $weekday);

                // If no hours, no calculations
                if (!$hours) {
                    continue;
                }

                /** @var Skin $frame */
                foreach ($frames as $frame) {
                    $playPerDay         = $openLengths[$weekday] * 60_000 / ($frame->loop_policy->max_duration_msec);
                    $impressionsPerPlay = $impressionsPerDay / $playPerDay;

                    $playsPerHour = 3_600_000 /* 3600 * 1000 (ms) */ / $frame->loop_policy->default_slot_duration;

                    $impressionsPerHour = round($impressionsPerPlay * $playsPerHour);

                    $sheet->printRow([
                        $location->external_id,
                        $frame->id,
                        $date->toDateString(),
                        $date->clone()->endOfDay()->toDateString(),
                        $hours->open_at->toTimeString(),
                        $hours->close_at->toTimeString(),
                        $i == 0 ? 1 : 0, // Monday
                        $i == 1 ? 1 : 0, // Tuesday
                        $i == 2 ? 1 : 0, // Wednesday
                        $i == 3 ? 1 : 0, // Thursday
                        $i == 4 ? 1 : 0, // Friday
                        $i == 5 ? 1 : 0, // Saturday
                        $i == 6 ? 1 : 0, // Sunday
                        $impressionsPerHour
                    ]);
                }
            }

            $datePointer->addWeek();
        } while ($datePointer->isBefore($endBoundary));

        $writer = new Csv($doc);
        $writer->setPreCalculateFormulas(false);

        header("access-control-allow-origin: *");
        header("content-type: text/csv");

        $writer->save("php://output");
        exit;
    }
}
