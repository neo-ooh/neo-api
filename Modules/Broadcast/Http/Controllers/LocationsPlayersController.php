<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LocationsPlayersController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Broadcast\Http\Requests\LocationsPlayers\ListLocationPlayersRequest;
use Neo\Modules\Broadcast\Models\Location;

class LocationsPlayersController {
    public function index(ListLocationPlayersRequest $request, Location $location): Response {
        return new Response($location->players);
    }
}
