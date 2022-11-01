<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterLocations.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\DisplayType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Location;
use Neo\Modules\Broadcast\Services\Resources\OpeningHours;
use Neo\Modules\Broadcast\Services\Resources\Player;
use Traversable;

interface BroadcasterLocations {
    /**
     * List all locations of the network.
     *
     * @return Traversable<Location> All locations of the network
     */
    public function listLocations(): Traversable;

    /**
     * List all players of the network
     *
     * @return iterable<Player>
     */
    public function listPlayers(): iterable;

    /**
     * @param ExternalBroadcasterResourceId $displayType
     * @return DisplayType|null
     */
    public function getDisplayType(ExternalBroadcasterResourceId $displayType): DisplayType|null;

    /**
     * @param ExternalBroadcasterResourceId $location
     * @return Location
     */
    public function getLocation(ExternalBroadcasterResourceId $location): Location;

    /**
     * Get the opening hours for the given location
     *
     * @param ExternalBroadcasterResourceId $location
     * @return OpeningHours
     */
    public function getLocationOpeningHours(ExternalBroadcasterResourceId $location): OpeningHours;

    /**
     * Set the opening hours of the given location
     *
     * @param ExternalBroadcasterResourceId $location
     * @param OpeningHours                  $openingHours
     * @return bool
     */
    public function setLocationOpeningHours(ExternalBroadcasterResourceId $location, OpeningHours $openingHours): bool;
}
