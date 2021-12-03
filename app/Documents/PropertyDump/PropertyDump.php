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


use Illuminate\Support\Collection;
use Neo\Documents\XLSX\XLSXDocument;
use Neo\Models\Location;
use Neo\Models\Player;
use Neo\Models\Property;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\Broadcaster;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Models\Format;

class PropertyDump extends XLSXDocument {

    protected array $columns = [
        "Venue Name",
        "Display Unit ID",
        "Player ID",
        "Screens",
        "Width",
        "Height",
        "Resolution",
        "Longitude",
        "Latitude",
        "Weekly Traffic",
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

        /** @var Location $location */
        foreach ($this->property->actor->own_locations as $location) {
            $displayUnitPlayersData = collect();

            /** @var Player $player */
            foreach ($location->players as $player) {
                // Start by pulling the player from Broadsign. We ignore non-BroadSign players
                $config = Broadcast::network($player->external_id)->getConfig();

                if ($config->broadcaster !== Broadcaster::BROADSIGN) {
                    continue;
                }

                $client = new BroadsignClient($config);

                $bsPlayer     = \Neo\Services\Broadcast\BroadSign\Models\Player::get($client, $player->external_id);
                $bsDiplayType = Format::get($client, $location->display_type->external_id);

                $displayUnitPlayersData->push([
                    "Venue Name"      => $this->property->actor->name,
                    "Display Unit ID" => $location->external_id,
                    "Player ID"       => $player->external_id,
                    "Screens"         => $bsPlayer->nscreens,
                    "Width"           => $bsDiplayType->res_width,
                    "Height"          => $bsDiplayType->res_height,
                    "Resolution"      => $bsDiplayType->res_width . "x" . $bsDiplayType->res_height,
                    "Longitude"       => $this->property->address->geolocation->getLng(),
                    "Latitude"        => $this->property->address->geolocation->getLat(),
                    "Weekly Traffic"  => $weeklyTraffic,
                ]);
            }

            $this->playersData->push(...$displayUnitPlayersData);
            $this->displayUnitsData->push([
                "Venue Name"      => $this->property->actor->name,
                "Display Unit ID" => $location->external_id,
                "Screens"         => $displayUnitPlayersData->sum("Screens"),
                "Width"           => $displayUnitPlayersData->first()["Width"],
                "Height"          => $displayUnitPlayersData->first()["Height"],
                "Resolution"      => $displayUnitPlayersData->first()["Resolution"],
                "Longitude"       => $this->property->address->geolocation->getLng(),
                "Latitude"        => $this->property->address->geolocation->getLat(),
                "Weekly Traffic"  => $weeklyTraffic,
            ]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function build(): bool {
        // Print our headers
        $this->ws->printRow([
            "Venue Name",
            "Display Unit ID",
            "Screens",
            "Width",
            "Height",
            "Resolution",
            "Longitude",
            "Latitude",
            "Weekly Traffic",
        ]);

        // Print each display unit
        foreach ($this->displayUnitsData as $displayUnitsDatum) {
            $this->ws->printRow($displayUnitsDatum);
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
