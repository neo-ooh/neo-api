<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BuildPropertyRows.php
 */

namespace Neo\Documents\PropertyDump;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
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

/**
 * @param int $propertyId
 * @return [array, array]
 */
function buildPropertyRows(Property $property) {
    $displayUnitsRows = collect();
    $playersRows      = collect();

    $weeklyTraffic = collect($property->traffic->getRollingWeeklyTraffic($property->network_id))->median();

    $addressComponents = [
        "Address"      => trim($property->address->line_1 . " " . $property->address->line_2),
        "City"         => $property->address->city->name,
        "Province"     => $property->address->city->province->slug,
        "Country"      => $property->address->city->province->country->slug,
        "Postal Code"  => $property->address->zipcode,
        "Full Address" => $property->address->string_representation,
        "Longitude"    => $property->address->geolocation->getLng(),
        "Latitude"     => $property->address->geolocation->getLat(),
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

        $impressionsPerWeek    = getPropertyImpressionsForBroadSign($client, $location, $openLengths, $weeklyTraffic);
        $totalScreens          = $bsPlayers->sum("nscreens");
        $impressionsPerScreens = $impressionsPerWeek / $totalScreens;

        /** @var Player $player */
        foreach ($location->players as $player) {
            $player = $bsPlayers->firstWhere("id", "=", $player->external_id);

            $displayUnitPlayersData->push(array_merge([
                "Venue Name"      => $property->actor->name,
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

        $playersRows->push(...$displayUnitPlayersData);
        $displayUnitsRows->push(array_merge([
            "Venue Name"      => $property->actor->name,
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

    return [$displayUnitsRows, $playersRows];
}


/** @noinspection PhpSuspiciousNameCombinationInspection */
function getPropertyImpressionsForBroadSign(BroadSignClient $client, Location $location, float $openLength, float $weeklyTraffic) {
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
