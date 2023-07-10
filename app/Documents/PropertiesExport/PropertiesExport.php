<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesExport.php
 */

namespace Neo\Documents\PropertiesExport;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\LoopConfiguration;
use Neo\Modules\Properties\Models\ImpressionsModel;
use Neo\Modules\Properties\Models\OpeningHours;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\PricelistProduct;
use Neo\Modules\Properties\Models\PricelistProductsCategory;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class PropertiesExport extends XLSXDocument {
    protected array $columns = [
        // Row start
        "Type",
        "Property Id",
        "Property Name",
        "Product Id",
        "Product Name",
        "Product Type",
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
        "Total Weekly Hours",
        // Price
        "CPM",
        // Traffic and impressions
        "Weekly Traffic",
        "Weekly Impressions",
        "Single Screen Weekly Impressions",
        // Display properties
        "Screens",
        "Width",
        "Height",
        "Resolution",
        // Broadcast
        "Spot Length (sec)",
        "Loop Length (sec)",
    ];

    protected ExportLevel|null $level;

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
                                               "products.pricelist",
                                               "traffic.weekly_data",
                                               "address",
                                               "opening_hours",
                                           ])
                                    ->findMany($data["properties"]);
        $this->level      = ExportLevel::tryFrom($data["level"]);

        return true;
    }

    protected function isLevel(ExportLevel $level): bool {
        return $this->level === null || $this->level === $level;
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
                "Postal Code"  => implode(" ", str_split($property->address?->zipcode, 3)),
                "Full Address" => $property->address?->string_representation ?? "",
                "Longitude"    => $property->address?->geolocation?->getCoordinates()[0] ?? "",
                "Latitude"     => $property->address?->geolocation?->getCoordinates()[1] ?? "",
            ];

            $openingHours = $property->opening_hours->keyBy("weekday");

            $operatingHoursComponents = [
                "Monday Open"     => $openingHours->has(1) ? ($openingHours[1]->is_closed ? "-" : $openingHours[1]->open_at->toTimeString('minutes')) : "00:00",
                "Monday Close"    => $openingHours->has(1) ? ($openingHours[1]->is_closed ? "-" : $openingHours[1]->close_at->toTimeString('minutes')) : "23:59",
                "Tuesday Open"    => $openingHours->has(2) ? ($openingHours[2]->is_closed ? "-" : $openingHours[2]->open_at->toTimeString('minutes')) : "00:00",
                "Tuesday Close"   => $openingHours->has(2) ? ($openingHours[2]->is_closed ? "-" : $openingHours[2]->close_at->toTimeString('minutes')) : "23:59",
                "Wednesday Open"  => $openingHours->has(3) ? ($openingHours[3]->is_closed ? "-" : $openingHours[3]->open_at->toTimeString('minutes')) : "00:00",
                "Wednesday Close" => $openingHours->has(3) ? ($openingHours[3]->is_closed ? "-" : $openingHours[3]->close_at->toTimeString('minutes')) : "23:59",
                "Thursday Open"   => $openingHours->has(4) ? ($openingHours[4]->is_closed ? "-" : $openingHours[4]->open_at->toTimeString('minutes')) : "00:00",
                "Thursday Close"  => $openingHours->has(4) ? ($openingHours[4]->is_closed ? "-" : $openingHours[4]->close_at->toTimeString('minutes')) : "23:59",
                "Friday Open"     => $openingHours->has(5) ? ($openingHours[5]->is_closed ? "-" : $openingHours[5]->open_at->toTimeString('minutes')) : "00:00",
                "Friday Close"    => $openingHours->has(5) ? ($openingHours[5]->is_closed ? "-" : $openingHours[5]->close_at->toTimeString('minutes')) : "23:59",
                "Saturday Open"   => $openingHours->has(6) ? ($openingHours[6]->is_closed ? "-" : $openingHours[6]->open_at->toTimeString('minutes')) : "00:00",
                "Saturday Close"  => $openingHours->has(6) ? ($openingHours[6]->is_closed ? "-" : $openingHours[6]->close_at->toTimeString('minutes')) : "23:59",
                "Sunday Open"     => $openingHours->has(7) ? ($openingHours[7]->is_closed ? "-" : $openingHours[7]->open_at->toTimeString('minutes')) : "00:00",
                "Sunday Close"    => $openingHours->has(7) ? ($openingHours[7]->is_closed ? "-" : $openingHours[7]->close_at->toTimeString('minutes')) : "23:59",
                "Total Hours"     => $property->opening_hours->map(fn(OpeningHours $hours) => $hours->is_closed ? 0 : $hours->open_at->diffInHours($hours->close_at, true))
                                                             ->sum(),
            ];

            if ($operatingHoursComponents["Total Hours"] === 0) {
                $operatingHoursComponents["Total Hours"] = 168;
            }

            $weeklyTraffic = collect($property->traffic->getRollingWeeklyTraffic())->max();

            $productsRows = [];

            /** @var Product $product */
            foreach ($property->products as $product) {
                // Ignore bonus products
                if ($product->is_bonus) {
                    continue;
                }

                // We need to calculate how many impressions per week this product is generating, for all the spots is the loop.
                /** @var ImpressionsModel|null $impressionsModel */
                $impressionsModel = $product->getImpressionModel(Carbon::now());
                /** @var LoopConfiguration|null $loopConfiguration */
                $loopConfiguration = $product->getLoopConfiguration(Carbon::now());

                $productWeeklyImpressions = 0;

                if ($impressionsModel && $loopConfiguration) {
                    // We use the impression model to calculate how many impressions one spot in the loop generate
                    $el                          = new ExpressionLanguage();
                    $singleSpotWeeklyImpressions = $el->evaluate(
                        $impressionsModel->formula,
                        array_merge([
                                        "traffic" => $weeklyTraffic, "faces" => $product->quantity, "spots" => 1, "loopLengthMin" => $loopConfiguration->loop_length_ms / (1_000 * 60), // ms to minutes
                                    ], $impressionsModel->variables)
                    );

                    // And we use the loop configuration to multiply this number by the number of spots in the loop
                    $productWeeklyImpressions = $singleSpotWeeklyImpressions * $loopConfiguration->getSpotCount();
                }

                $productScreenCount = $product->locations->flatMap(fn(Location $location) => $location->players)
                                                         ->sum("screen_count");


                /** @var Pricelist|null $pricelist */
                $pricelist = $product->pricelist?->load(["categories_pricings", "products_pricings"]);
                /** @var PricelistProduct|PricelistProductsCategory|null $pricing */
                $pricing = $pricelist?->products_pricings->firstWhere("product_id", "=", $product->getKey())
                    ?? $pricelist?->categories_pricings->firstWhere("products_category_id", "=", $product->category_id);

                $locationsRows = [];
                $screensCount  = 0;

                /** @var Location $location */
                foreach ($product->locations as $location) {
                    $locationScreenCount = $location->players->sum("screen_count");
                    $screensCount        += $locationScreenCount;

                    $locationImpressionsShare = $productScreenCount > 0 ? $locationScreenCount / $productScreenCount : 0;

                    $playerRows = [];

                    if ($this->isLevel(ExportLevel::Players)) {
                        foreach ($location->players as $player) {
                            $playerImpressionsShare = $locationScreenCount > 0 ? $player->screen_count / $productScreenCount : 0;

                            $playerRows[] = ([
                                "player",
                                $property->getKey(),
                                $property->actor->name,
                                $product->getKey(),
                                $product->name_en,
                                $product->category->type->name,
                                $location->external_id,
                                $location->internal_name,
                                $player->external_id,
                                $player->name,
                                ...$addressComponents,
                                ...$operatingHoursComponents,
                                $pricing?->value ?? "-",
                                $weeklyTraffic,
                                round($productWeeklyImpressions * $playerImpressionsShare),
                                $player->screen_count > 0 ? round(($productWeeklyImpressions * $playerImpressionsShare) / $player->screen_count) : 0,
                                $player->screen_count,
                                $location->display_type->width_px,
                                $location->display_type->height_px,
                                $location->display_type->width_px . "x" . $location->display_type->height_px,
                                $loopConfiguration?->spot_length_ms / 1_000, // ms to sec
                                $loopConfiguration?->loop_length_ms / 1_000,// ms to sec
                            ]);
                        }
                    }

                    if ($this->isLevel(ExportLevel::Locations)) {
                        $locationRow = [
                            "location",
                            $property->getKey(),
                            $property->actor->name,
                            $product->getKey(),
                            $product->name_en,
                            $product->category->type->name,
                            $location->external_id,
                            $location->internal_name,
                            "",
                            "",
                            ...$addressComponents,
                            ...$operatingHoursComponents,
                            $pricing?->value ?? "-",
                            $weeklyTraffic,
                            round($productWeeklyImpressions * $locationImpressionsShare),
                            "",
                            $locationScreenCount,
                            $location->display_type->width_px,
                            $location->display_type->height_px,
                            $location->display_type->width_px . "x" . $location->display_type->height_px,
                            $loopConfiguration?->spot_length_ms / 1_000, // ms to sec
                            $loopConfiguration?->loop_length_ms / 1_000,// ms to sec
                        ];

                        $locationsRows[] = $locationRow;
                    }

                    array_push($locationsRows, ...$playerRows);
                }

                if ($this->isLevel(ExportLevel::Products)) {
                    $productRow = [
                        "product",
                        $property->getKey(),
                        $property->actor->name,
                        $product->getKey(),
                        $product->name_en,
                        $product->category->type->name,
                        "",
                        "",
                        "",
                        "",
                        ...$addressComponents,
                        ...$operatingHoursComponents,
                        $pricing?->value ?? "-",
                        $weeklyTraffic,
                        $productWeeklyImpressions,
                        "",
                        $screensCount,
                        $product->locations->first()?->display_type->width_px,
                        $product->locations->first()?->display_type->height_px,
                        $product->locations->first()?->display_type->width_px . "x" . $product->locations->first()?->display_type->height_px,
                        $loopConfiguration?->spot_length_ms / 1_000, // ms to sec
                        $loopConfiguration?->loop_length_ms / 1_000,// ms to sec
                    ];

                    $productsRows[] = $productRow;
                }

                array_push($productsRows, ...$locationsRows);
            }

            if ($this->isLevel(ExportLevel::Properties)) {
                $lines[] = [
                    "property",
                    $property->getKey(),
                    $property->actor->name,
                ];
            }

            array_push($lines, ...$productsRows);
        }

        // Print the header
        $this->ws->getStyle($this->ws->getRelativeRange(count($this->columns), 1))
                 ->applyFromArray(XLSXStyleFactory::tableHeader());
        $this->ws->printRow($this->columns);

        $this->ws->fromArray($lines);

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
