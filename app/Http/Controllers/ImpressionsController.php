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
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Neo\Documents\XLSX\Worksheet;
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
        $displayUnitId = (int)$displayUnitId;

        /** @var Location|null $location */
        $location = Location::query()->where("external_id", "=", $displayUnitId)->first();


        if (!$location) {
            Log::warning("[ImpressionsController] Unknown display Unit ID: $displayUnitId");
            return new Response([
                "error"   => true,
                "type"    => "unknown-value",
                "message" => "The provided Display Unit Id is not registered on Connect.",
            ], 400);
        }

        $config = Broadcast::network($location->network_id)->getConfig();

        if (!($config instanceof BroadSignConfig)) {
            Log::warning("[ImpressionsController] Location #{$location->getKey()} ($location->name) is not a BroadSign display unit.");
            return new Response([
                "error"   => true,
                "type"    => "invalid-value",
                "message" => "The provided Display Unit Id is not a BroadSign Display Unit.",
            ], 400);
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
            Log::warning("[ImpressionsController] Location #{$location->getKey()} ($location->name) is not associated with any product");
            return new Response([
                "error"   => true,
                "type"    => "invalid-value",
                "message" => "The Display Unit is not associated with a product.",
            ], 400);
        }

        try {
            $this->buildBroadSignAudienceFile($client, $location, $product);
        } catch (Exception $e) {
            Log::error("[ImpressionsController] {$e->__toString()}");
        }
        exit;
    }

    protected function buildBroadSignAudienceFile(BroadsignClient $client, Location $location, Product $product): void {

        /** @var Property $property */
        $property                         = Property::query()
                                                    ->with(["opening_hours", "traffic.weekly_data"])
                                                    ->find($product->property_id);
        $property->rolling_weekly_traffic = $property->traffic->getRollingWeeklyTraffic($property->network_id);

        // Load all the frames, of the display unit, and load their loop policies as well
        $frames = Skin::byDisplayUnit($client, ["display_unit_id" => $location->external_id]);
        $frames->each(/**
         * @param Skin $frame
         */ function (Skin $frame) use ($client) {
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
        $endBoundary = $datePointer->clone()->addMonth();

        // Dump a failover row for every frame
        foreach ($frames as $frame) {
            $sheet->printRow([
                $location->external_id,
                $frame->id,
                "",
                "",
                "",
                "",
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
         */ fn(OpeningHours $hours) => [$hours->weekday => $hours->open_at->diffInMinutes($hours->close_at, true)]);

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
                    /**
                     * @var LoopPolicy $loopPolicy
                     */
                    $loopPolicy = $frame->loop_policy;

                    if (($loopPolicy->max_duration_msec ?? $loopPolicy->default_slot_duration) == 0) {
                        Log::error("[ImpressionsController] Unusable LoopPolicy: " . json_encode($loopPolicy, JSON_THROW_ON_ERROR));
                    }

                    $playPerDay         = $openLengths[$weekday] * 60_000 / ($loopPolicy->max_duration_msec ?? $loopPolicy->default_slot_duration);
                    $impressionsPerPlay = $impressionsPerDay / $playPerDay;

                    $playsPerHour = 3_600_000 /* 3600 * 1000 (ms) */ / $loopPolicy->primary_inventory_share_msec;

                    $impressionsPerHour = ceil($impressionsPerPlay * $playsPerHour) + 2; // This +2 is to be `extra-generous` on the number of impressions delivered

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
        $writer->setEnclosure('');
        $writer->setPreCalculateFormulas(false);

        header("access-control-allow-origin: *");
        header("content-type: text/csv");

        $writer->save("php://output");
    }
}
