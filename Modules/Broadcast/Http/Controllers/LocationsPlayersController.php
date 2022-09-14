<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LocationsPlayersController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\LocationsPlayers\ListLocationPlayersRequest;
use Neo\Modules\Broadcast\Models\Location;

class LocationsPlayersController {
    public function index(ListLocationPlayersRequest $request, Location $location) {
        return new Response($location->players);
    }
}
