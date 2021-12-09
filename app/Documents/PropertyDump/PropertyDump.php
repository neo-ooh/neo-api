<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyDump.php
 */

namespace Neo\Documents\PropertyDump;


use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Models\Location;
use Neo\Models\OpeningHours;
use Neo\Models\Player;
use Neo\Models\Product;
use Neo\Models\Property;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\Broadcaster;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Models\DayPart;
use Neo\Services\Broadcast\BroadSign\Models\Format;
use Neo\Services\Broadcast\BroadSign\Models\LoopPolicy;
use Neo\Services\Broadcast\BroadSign\Models\Player as BSPlayer;
use Neo\Services\Broadcast\BroadSign\Models\Skin;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class PropertyDump extends XLSXDocument {
    protected array $columns = [
        "Venue Name",
        "Display Unit ID",
        "Player ID",
        "Name",
        "Screens",
        "Width",
        "Height",
        "Resolution",
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
        "Weekly Traffic",
        "Weekly Impressions",
        "Weekly Impressions per screen",
    ];

    protected Property $property;

    protected Collection $displayUnitsData;
    protected Collection $playersData;

    public function __construct(protected int $propertyId) {
        parent::__construct();
        $this->ingest(null);
    }

    /**
     * @inheritDoc
     * @noinspection PhpSuspiciousNameCombinationInspection
     */
    protected function ingest($data): bool {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->property = Property::query()
                                  ->with([
                                      "actor",
                                      "actor.own_locations",
                                      "actor.own_locations.players",
                                      "actor.own_locations.display_type",
                                      "traffic",
                                      "traffic.weekly_data",
                                      "address",
                                      "opening_hours",
                                      "products",
                                      "products.impressions_models",
                                      "products_categories.impressions_models"
                                  ])
                                  ->find($this->propertyId);

        if (!$this->property) {
            return false;
        }

        $this->displayUnitsData = collect();
        $this->playersData      = collect();

        $weeklyTraffic = collect($this->property->traffic->getRollingWeeklyTraffic())->median();

        $addressComponents = [
            "Address"      => trim($this->property->address->line_1 . " " . $this->property->address->line_2),
            "City"         => $this->property->address->city->name,
            "Province"     => $this->property->address->city->province->slug,
            "Country"      => $this->property->address->city->province->country->slug,
            "Postal Code"  => $this->property->address->zipcode,
            "Full Address" => $this->property->address->string_representation,
            "Longitude"    => $this->property->address->geolocation->getLng(),
            "Latitude"     => $this->property->address->geolocation->getLat(),
        ];

        $operatingHoursComponents = [
            "Monday Open"     => $this->property->opening_hours->firstWhere("weekday", "=", 1)->open_at->toTimeString('minutes'),
            "Monday Close"    => $this->property->opening_hours->firstWhere("weekday", "=", 1)->close_at->toTimeString('minutes'),
            "Tuesday Open"    => $this->property->opening_hours->firstWhere("weekday", "=", 2)->open_at->toTimeString('minutes'),
            "Tuesday Close"   => $this->property->opening_hours->firstWhere("weekday", "=", 2)->close_at->toTimeString('minutes'),
            "Wednesday Open"  => $this->property->opening_hours->firstWhere("weekday", "=", 3)->open_at->toTimeString('minutes'),
            "Wednesday Close" => $this->property->opening_hours->firstWhere("weekday", "=", 3)->close_at->toTimeString('minutes'),
            "Thursday Open"   => $this->property->opening_hours->firstWhere("weekday", "=", 4)->open_at->toTimeString('minutes'),
            "Thursday Close"  => $this->property->opening_hours->firstWhere("weekday", "=", 4)->close_at->toTimeString('minutes'),
            "Friday Open"     => $this->property->opening_hours->firstWhere("weekday", "=", 5)->open_at->toTimeString('minutes'),
            "Friday Close"    => $this->property->opening_hours->firstWhere("weekday", "=", 5)->close_at->toTimeString('minutes'),
            "Saturday Open"   => $this->property->opening_hours->firstWhere("weekday", "=", 6)->open_at->toTimeString('minutes'),
            "Saturday Close"  => $this->property->opening_hours->firstWhere("weekday", "=", 6)->close_at->toTimeString('minutes'),
            "Sunday Open"     => $this->property->opening_hours->firstWhere("weekday", "=", 7)->open_at->toTimeString('minutes'),
            "Sunday Close"    => $this->property->opening_hours->firstWhere("weekday", "=", 7)->close_at->toTimeString('minutes'),
            "Total Hours"     => $this->property->opening_hours->map(fn($hours) => $hours->open_at->floatDiffInHours($hours->close_at, true))
                                                               ->sum(),
        ];

        $openLengths = $this->property->opening_hours->map(
            fn(OpeningHours $hours) => $hours->open_at->diffInMinutes($hours->close_at, true)
        )->sum();

        /** @var Location $location */
        foreach ($this->property->actor->own_locations as $location) {
            $displayUnitPlayersData = collect();

            // Start by pulling the player from Broadsign. We ignore non-BroadSign locations
            $config = Broadcast::network($location->network_id)->getConfig();

            if ($config->broadcaster !== Broadcaster::BROADSIGN) {
                continue;
            }

            $client = new BroadsignClient($config);

            $bsDiplayType = Format::get($client, $location->display_type->external_id);
            /** @var Collection $bsPlayers */
            $bsPlayers = BSPlayer::getMultiple($client, $location->players->pluck("external_id")->toArray());

            $impressionsPerWeek    = $this->getImpressions($client, $location, $openLengths, $weeklyTraffic);
            $totalScreens          = $bsPlayers->sum("nscreens");
            $impressionsPerScreens = $impressionsPerWeek / $totalScreens;

            /** @var Player $player */
            foreach ($location->players as $player) {
                $player = $bsPlayers->firstWhere("id", "=", $player->external_id);

                $displayUnitPlayersData->push(array_merge([
                    "Venue Name"      => $this->property->actor->name,
                    "Display Unit ID" => $location->external_id,
                    "Player ID"       => $player->id,
                    "Name"            => $player->name,
                    "Screens"         => $player->nscreens,
                    "Width"           => $bsDiplayType->res_width,
                    "Height"          => $bsDiplayType->res_height,
                    "Resolution"      => $bsDiplayType->res_width . "x" . $bsDiplayType->res_height,
                ], $addressComponents, $operatingHoursComponents, [
                    "Weekly Traffic"                => $weeklyTraffic,
                    "Weekly Impressions"            => $impressionsPerScreens * $player->nscreens,
                    "Weekly Impressions per screen" => $impressionsPerScreens,
                ]));
            }

            $this->playersData->push(...$displayUnitPlayersData);
            $this->displayUnitsData->push(array_merge([
                "Venue Name"      => $this->property->actor->name,
                "Display Unit ID" => $location->external_id,
                "Name"            => $location->name,
                "Screens"         => $displayUnitPlayersData->sum("Screens"),
                "Width"           => $displayUnitPlayersData->first()["Width"],
                "Height"          => $displayUnitPlayersData->first()["Height"],
                "Resolution"      => $displayUnitPlayersData->first()["Resolution"]
            ], $addressComponents, $operatingHoursComponents, [
                "Weekly Traffic"     => $weeklyTraffic,
                "Weekly Impressions" => $impressionsPerWeek,
            ]));
        }

        return true;
    }

    /** @noinspection PhpSuspiciousNameCombinationInspection */
    protected function getImpressions(BroadSignClient $client, Location $location, float $openLength, float $weeklyTraffic) {
        $now = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $bsSkins    = Skin::byDisplayUnit($client, ["display_unit_id" => $location->external_id]);
        $bsDayParts = DayPart::getMultiple($client, $bsSkins->pluck("parent_id")->toArray());

        // Get the proper frame
        // 1. Filter by dates
        // 2. Then by name if required
        $skins = $bsSkins->filter(function (Skin $skin) use ($bsDayParts, $now) {
            /** @var DayPart $dayPart */
            $dayPart      = $bsDayParts->firstWhere("id", "=", $skin->parent_id);
            $dayPartStart = Carbon::parse($dayPart->virtual_start_date)->setYear($now->year);
            $dayPartEnd   = Carbon::parse($dayPart->virtual_end_date)->setYear($now->year);
            return $dayPartStart->isBefore($now) && $dayPartEnd->isAfter($now);
        });

        /** @var Skin $skin */
        $skin = $skins->first(fn(Skin $skin) => str_starts_with($skin->name, "Main") || str_starts_with($skin->name, "Primary"), $skins->first());

        // Calculate impressions
        /** @var Product $product */
        $product = $location->products->where("is_bonus", "=", false)->first();

        if (!$product || !($model = $product->getImpressionModel($now))) {
            return 0;
        }

        $el                         = new ExpressionLanguage();
        $impressionsPerWeekForOneAd = $el->evaluate($model->formula, array_merge([
            "traffic" => $weeklyTraffic,
            "faces"   => $product->quantity,
            "spots"   => 1
        ], $model->variables));

        /** @var LoopPolicy $loopPolicy */
        $loopPolicy = LoopPolicy::get($client, $skin->loop_policy_id);
        $adsPerLoop = $loopPolicy->max_duration_msec / $loopPolicy->default_slot_duration;

        return $impressionsPerWeekForOneAd * $adsPerLoop;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        $this->ws->setTitle("Display Units");
        $this->ws->getStyle($this->ws->getRelativeRange(33, 1))->applyFromArray(XLSXStyleFactory::tableHeader());

        // Print our headers
        $this->ws->printRow([
            "Venue Name",
            "Display Unit ID",
            "Name",
            "Screens",
            "Width",
            "Height",
            "Resolution",
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
            "Weekly Traffic",
            "Weekly Impressions",
        ]);

        // Print each display unit
        foreach ($this->displayUnitsData as $displayUnitsDatum) {
            $this->ws->printRow($displayUnitsDatum);
        }

        // Resize columns
        foreach ($this->columns as $i => $ignored) {
            $this->ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $this->worksheet = new Worksheet(null, "Players");
        $this->spreadsheet->addSheet($this->worksheet);
        $this->spreadsheet->setActiveSheetIndexByName("Players");

        $this->ws->getStyle($this->ws->getRelativeRange(34, 1))->applyFromArray(XLSXStyleFactory::tableHeader());

        // Print our headers
        $this->ws->printRow([
            "Venue Name",
            "Display Unit ID",
            "Player ID",
            "Name",
            "Screens",
            "Width",
            "Height",
            "Resolution",
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
            "Weekly Traffic",
            "Weekly Impressions",
            "Weekly Impressions per screen",
        ]);

        // Print each display unit
        foreach ($this->playersData as $playersDatum) {
            $this->ws->printRow($playersDatum);
        }

        // Resize columns
        foreach ($this->columns as $i => $ignored) {
            $this->ws->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return "Property Dump";
    }
}
