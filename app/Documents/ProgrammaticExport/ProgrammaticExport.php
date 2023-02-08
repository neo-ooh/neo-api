<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProgrammaticExport.php
 */

namespace Neo\Documents\ProgrammaticExport;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\OpeningHours;
use Neo\Models\Product;
use Neo\Models\Property;
use Neo\Modules\Broadcast\Models\Location;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ProgrammaticExport extends XLSXDocument {
    protected array $columns = [
        // Row start
        "Type",
        "Property Id",
        "Property Name",
        "Product Id",
        "Product Name",
        "Display Unit Id",
        "Display Unit Name",
        "Player Id",
        "Player Name",
        // Property properties
        "Address",
        "City",
        "Province",
        "Country",
        "Postal Code",
        "Full Address",
        "Longitude",
        "Latitude",
        "Monday Open",
        "Monday Close",
        "Tuesday Open",
        "Tuesday Close",
        "Wednesday Open",
        "Wednesday Close",
        "Thursday Open",
        "Thursday Close",
        "Friday Open",
        "Friday Close",
        "Saturday Open",
        "Saturday Close",
        "Sunday Open",
        "Sunday Close",
        "Total Hours",
        // Traffic and impressions
        "Weekly Traffic",
        "Weekly Impressions",
        "Single Screen Weekly Impressions",
        // Display properties
        "Screens",
        "Width",
        "Height",
        "Resolution",
    ];

    protected Collection $properties;

    protected Collection $displayTypes;

    protected Collection $players;

    /**
     * @inheritDoc
     */
    protected function ingest($data): bool {
        $this->properties = Property::query()
                                    ->with([
                                               "actor",
                                               "products.locations.display_type",
                                               "products.locations.players",
                                               "products.impressions_models",
                                               "products.category.impressions_models",
                                               "traffic.weekly_data",
                                               "address",
                                               "opening_hours",
                                           ])
                                    ->findMany($data);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        // | Property
        // | - Product
        // | - - Display Unit
        // | - - - Player

        $this->ws->setTitle("Display Units");

        // Build our lines
        $lines = [];

        /** @var Property $property */
        foreach ($this->properties as $property) {
            $addressComponents = [
                "Address"      => trim($property->address?->line_1 . " " . ($property->address?->line_2 ?: "")),
                "City"         => $property->address?->city->name,
                "Province"     => $property->address?->city->province->slug,
                "Country"      => $property->address?->city->province->country->slug,
                "Postal Code"  => $property->address?->zipcode,
                "Full Address" => $property->address?->string_representation,
                "Longitude"    => $property->address?->geolocation->getLng(),
                "Latitude"     => $property->address?->geolocation->getLat(),
            ];

            $operatingHoursComponents = [
                "Monday Open"     => $property->opening_hours->firstWhere("weekday", "=", 1)?->open_at->toTimeString('minutes'),
                "Monday Close"    => $property->opening_hours->firstWhere("weekday", "=", 1)?->close_at->toTimeString('minutes'),
                "Tuesday Open"    => $property->opening_hours->firstWhere("weekday", "=", 2)?->open_at->toTimeString('minutes'),
                "Tuesday Close"   => $property->opening_hours->firstWhere("weekday", "=", 2)?->close_at->toTimeString('minutes'),
                "Wednesday Open"  => $property->opening_hours->firstWhere("weekday", "=", 3)?->open_at->toTimeString('minutes'),
                "Wednesday Close" => $property->opening_hours->firstWhere("weekday", "=", 3)?->close_at->toTimeString('minutes'),
                "Thursday Open"   => $property->opening_hours->firstWhere("weekday", "=", 4)?->open_at->toTimeString('minutes'),
                "Thursday Close"  => $property->opening_hours->firstWhere("weekday", "=", 4)?->close_at->toTimeString('minutes'),
                "Friday Open"     => $property->opening_hours->firstWhere("weekday", "=", 5)?->open_at->toTimeString('minutes'),
                "Friday Close"    => $property->opening_hours->firstWhere("weekday", "=", 5)?->close_at->toTimeString('minutes'),
                "Saturday Open"   => $property->opening_hours->firstWhere("weekday", "=", 6)?->open_at->toTimeString('minutes'),
                "Saturday Close"  => $property->opening_hours->firstWhere("weekday", "=", 6)?->close_at->toTimeString('minutes'),
                "Sunday Open"     => $property->opening_hours->firstWhere("weekday", "=", 7)?->open_at->toTimeString('minutes'),
                "Sunday Close"    => $property->opening_hours->firstWhere("weekday", "=", 7)?->close_at->toTimeString('minutes'),
                "Total Hours"     => $property->opening_hours->map(fn(OpeningHours $hours) => $hours->open_at->floatDiffInHours($hours->close_at, true))
                                                             ->sum(),
            ];

            $weeklyTraffic = collect($property->traffic->getRollingWeeklyTraffic($property->network_id))->median();

            $propertyLines = [];

            /** @var Product $product */
            foreach ($property->products as $product) {
                // Ignore bonus products
                if ($product->is_bonus || $product->locations->count() === 0) {
                    continue;
                }

                // We need to calculate how many impressions per week this product is generating, for all the spots is the loop.
                $impressionsModel  = $product->getImpressionModel(Carbon::now());
                $loopConfiguration = $product->getLoopConfiguration(Carbon::now());

                $productWeeklyImpressions = 0;

                if ($impressionsModel && $loopConfiguration) {
                    // We use the impression model to calculate how many impressions one spot in the loop generate
                    $el                          = new ExpressionLanguage();
                    $singleSpotWeeklyImpressions = $el->evaluate(
                        $impressionsModel->formula,
                        array_merge([
                                        "traffic" => $weeklyTraffic,
                                        "faces"   => $product->quantity,
                                        "spots"   => 1,
                                    ], $impressionsModel->variables)
                    );

                    // And we use the loop configuration to multiply this number by the number of spots in the loop
                    $productWeeklyImpressions = $singleSpotWeeklyImpressions * $loopConfiguration->getSpotCount();
                }

                $productScreenCount = $product->locations->flatMap(fn(Location $location) => $location->players)
                                                         ->sum("screen_count");

                $productRows = [];

                /** @var Location $location */
                foreach ($product->locations as $location) {
                    $locationScreenCount      = $location->players->sum("screen_count");
                    $locationImpressionsShare = $productScreenCount > 0 ? $locationScreenCount / $productScreenCount : 0;

                    $playerRows = [];

                    foreach ($location->players as $player) {
                        $playerImpressionsShare = $locationScreenCount > 0 ? $player->screen_count / $productScreenCount : 0;

                        $playerRows[] = ([
                            "player",
                            $property->getKey(),
                            $property->actor->name,
                            $product->getKey(),
                            $product->name_en,
                            $location->external_id,
                            $location->internal_name,
                            $player->external_id,
                            $player->name,
                            ...$addressComponents,
                            ...$operatingHoursComponents,
                            $weeklyTraffic,
                            round($productWeeklyImpressions * $playerImpressionsShare),
                            round(($productWeeklyImpressions * $playerImpressionsShare) / $player->screen_count),
                            $player->screen_count,
                            $location->display_type->width_px,
                            $location->display_type->height_px,
                            $location->display_type->width_px . "x" . $location->display_type->height_px,
                        ]);
                    }

                    $locationRow = [
                        "location",
                        $property->getKey(),
                        $property->actor->name,
                        $product->getKey(),
                        $product->name_en,
                        $location->external_id,
                        $location->internal_name,
                        "",
                        "",
                        ...$addressComponents,
                        ...$operatingHoursComponents,
                        $weeklyTraffic,
                        round($productWeeklyImpressions * $locationImpressionsShare),
                        "",
                        $locationScreenCount,
                        $location->display_type->width_px,
                        $location->display_type->height_px,
                        $location->display_type->width_px . "x" . $location->display_type->height_px,
                    ];

                    array_push($productRows, $locationRow, ...$playerRows);
                }

                $productRow = [
                    "product",
                    $property->getKey(),
                    $property->actor->name,
                    $product->getKey(),
                    $product->name_en,
                ];

                array_push($propertyLines, $productRow, ...$productRows);
            }

            if (count($propertyLines) > 0) {
                array_push($lines, [
                    "property",
                    $property->getKey(),
                    $property->actor->name,
                ], ...     $propertyLines);
            }
        }

        // Print the header
        $this->ws->getStyle($this->ws->getRelativeRange(count($this->columns), 1))
                 ->applyFromArray(XLSXStyleFactory::tableHeader());
        $this->ws->printRow($this->columns);

        foreach ($lines as $line) {
            $lineStyle = match ($line[0]) {
                "property" => XLSXStyleFactory::programmaticPropertyRow(),
                "product"  => XLSXStyleFactory::programmaticProductRow(),
                "location" => XLSXStyleFactory::programmaticLocationRow(),
                default    => null,
            };

            if ($lineStyle) {
                $this->ws->getStyle($this->ws->getRelativeRange(count($this->columns), 1))
                         ->applyFromArray($lineStyle);
            }

            $this->ws->printRow($line);
        }

        // Resize columns
        foreach ($this->columns as $i => $ignored) {
            $this->ws->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        if ($this->properties->count() === 1) {
            return $this->properties->first()->actor->name();
        }

        return "ProgrammaticsExport";
    }
}
