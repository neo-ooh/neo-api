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
use Exception;
use Illuminate\Support\Collection;
use Neo\Documents\XLSX\Worksheet;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Documents\XLSX\XLSXStyleFactory;
use Neo\Exceptions\InvalidBroadcastServiceException;
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

    protected \Illuminate\Database\Eloquent\Collection $properties;

    protected Collection $displayTypes;
    protected Collection $players;
    protected Collection $dayParts;
    protected Collection $skins;
    protected Collection $loopPolicies;

    protected Collection $displayUnitsRows;
    protected Collection $playersRows;

    public function __construct(protected array $propertiesId) {
        parent::__construct();
        $this->ingest(null);
    }

    /**
     * @inheritDoc
     * @throws InvalidBroadcastServiceException
     */
    protected function ingest($data): bool {
        $this->properties = Property::query()
                                    ->with([
                                        "actor",
                                        "actor.own_locations",
                                        "actor.own_locations.products",
                                        "actor.own_locations.products.impressions_models",
                                        "actor.own_locations.products.category",
                                        "actor.own_locations.products.category.impressions_models",
                                        "actor.own_locations.players",
                                        "actor.own_locations.display_type",
                                        "traffic",
                                        "traffic.weekly_data",
                                        "address",
                                        "opening_hours",
                                    ])
                                    ->whereIn("actor_id", $this->propertiesId)
                                    ->get();

        $this->displayUnitsRows = collect();
        $this->playersRows      = collect();

        $config = Broadcast::network($this->properties->first()->network_id)->getConfig();

        if ($config->broadcaster !== Broadcaster::BROADSIGN) {
            throw new Exception("Only Broadsign properties are supported");
        }

        $client = new BroadsignClient($config);

        $this->displayTypes = Format::getMultiple($client, $this->properties
            ->flatMap(fn($property) => $property->actor
                ->own_locations
                ->pluck("display_type.external_id")
            )->unique()->toArray());

        $this->players = BSPlayer::getMultiple($client, $this->properties
            ->flatMap(fn($property) => $property->actor
                ->own_locations
                ->flatMap(fn($location) => $location->players->pluck("external_id"))
            )->unique()->toArray());

        $this->dayParts     = DayPart::all($client);
        $this->skins        = Skin::all($client);
        $this->loopPolicies = LoopPolicy::all($client);

        foreach ($this->properties as $property) {
            if (!$property) {
                return false;
            }

            [$displayUnitsRows, $playersRows] = $this->buildPropertyRows($property, $client);

            $this->displayUnitsRows->push(...$displayUnitsRows);
            $this->playersRows->push(...$playersRows);
        }

        return true;
    }

    protected function buildPropertyRows(Property $property, BroadsignClient $client): array {
        $displayUnitsRows = collect();
        $playersRows      = collect();

        $weeklyTraffic = collect($property->traffic->getRollingWeeklyTraffic($property->network_id))->median();

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
            "Total Hours"     => $property->opening_hours->map(fn($hours) => $hours->open_at->floatDiffInHours($hours->close_at, true))
                                                         ->sum(),
        ];

        $openLengths = $property->opening_hours->map(
            fn(OpeningHours $hours) => $hours->open_at->diffInMinutes($hours->close_at, true)
        )->sum();

        /** @var Location $location */
        foreach ($property->actor->own_locations as $location) {
            // Skip locations not associated with products, or locations without any player
            if ($location->products->count() === 0 || $location->players->count() === 0) {
                continue;
            }

            $displayUnitPlayersData = collect();

            $bsDisplayType = $this->displayTypes->firstWhere("id", "=", $location->display_type->external_id);
            $bsPlayers     = $this->players->whereIn("id", $location->players->pluck("external_id"));

            $impressionsPerWeek = $this->getPropertyImpressionsForBroadSign($client, $location, $openLengths, $weeklyTraffic);

            $productScreens     = $location->products->where("is_bonus", "=", false)->first()->quantity;
            $displayUnitScreens = $bsPlayers->sum("nscreens");

            // If this display unit is configured as having no screens, we skip it
            if ($displayUnitScreens === 0) {
                continue;
            }

            $displayUnitShares      = $displayUnitScreens / $productScreens;
            $displayUnitImpressions = $impressionsPerWeek * $displayUnitShares;
            $impressionsPerScreens  = $displayUnitImpressions / $displayUnitScreens;

            /** @var Player $player */
            foreach ($location->players as $player) {
                $player = $bsPlayers->firstWhere("id", "=", $player->external_id);

                $displayUnitPlayersData->push(array_merge([
                    "Venue Name"      => $property->actor->name,
                    "Display Unit ID" => $location->external_id,
                    "Player ID"       => $player->id,
                    "Display Unit"    => $location->name,
                    "Name"            => $player->name,
                    "Screens"         => $player->nscreens,
                    "Width"           => $bsDisplayType->res_width,
                    "Height"          => $bsDisplayType->res_height,
                    "Resolution"      => $bsDisplayType->res_width . "x" . $bsDisplayType->res_height,
                ], $addressComponents, $operatingHoursComponents, [
                    "Weekly Traffic"                => $weeklyTraffic,
                    "Weekly Impressions"            => $impressionsPerScreens * $player->nscreens,
                    "Weekly Impressions per screen" => $impressionsPerScreens,
                ]));
            }

            $playersRows->push(...$displayUnitPlayersData);
            $displayUnitsRows->push(array_merge([
                "Venue Name"      => $property->actor->name,
                "Display Unit ID" => $location->external_id,
                "Name"            => $location->name,
                "Screens"         => $displayUnitPlayersData->sum("Screens"),
                "Width"           => $displayUnitPlayersData->first()["Width"],
                "Height"          => $displayUnitPlayersData->first()["Height"],
                "Resolution"      => $displayUnitPlayersData->first()["Resolution"],
            ], $addressComponents, $operatingHoursComponents, [
                "Weekly Traffic"     => $weeklyTraffic,
                "Weekly Impressions" => $displayUnitImpressions,
            ]));
        }

        return [$displayUnitsRows, $playersRows];
    }


    /** @noinspection PhpSuspiciousNameCombinationInspection */
    protected function getPropertyImpressionsForBroadSign(BroadSignClient $client, Location $location, float $openLength, float $weeklyTraffic) {
        $now = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);

        $bsDayParts = $this->dayParts->where("parent_id", "=", $location->external_id);
        $bsSkins    = $this->skins->whereIn("parent_id", $bsDayParts->pluck("id"));

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
            "spots"   => 1,
        ], $model->variables));

        /** @var LoopPolicy $loopPolicy */
        $loopPolicy = $this->loopPolicies->firstWhere("id", "=", $skin->loop_policy_id);
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
        foreach ($this->displayUnitsRows as $displayUnitsDatum) {
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
            "Display Unit",
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
        foreach ($this->playersRows as $playersDatum) {
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
