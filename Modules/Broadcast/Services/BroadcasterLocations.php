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

use Neo\Modules\Broadcast\Services\Resources\ActiveHours;
use Neo\Modules\Broadcast\Services\Resources\DisplayType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Location;
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
     * Get the active hours for the given location
     *
     * @param ExternalBroadcasterResourceId $location
     * @return ActiveHours
     */
    public function getLocationActiveHours(ExternalBroadcasterResourceId $location): ActiveHours;

    /**
     * Set the active hours of the given location
     *
     * @param ExternalBroadcasterResourceId $location
     * @param ActiveHours                   $activeHours
     * @return bool
     */
    public function setLocationActiveHours(ExternalBroadcasterResourceId $location, ActiveHours $activeHours): bool;
}
