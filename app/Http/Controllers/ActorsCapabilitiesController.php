<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsCapabilitiesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsCapabilities\ListActorCapabilitiesRequest;
use Neo\Http\Requests\ActorsCapabilities\SyncActorCapabilitiesRequest;
use Neo\Models\Actor;

class ActorsCapabilitiesController extends Controller {
    public function index(ListActorCapabilitiesRequest $request, Actor $actor): Response {
        $capabilities = $actor->standalone_capabilities;

        return new Response($capabilities);
    }

    public function sync(SyncActorCapabilitiesRequest $request, Actor $actor): Response {
        $actor->standalone_capabilities()->sync($request->input("capabilities"));

        return new Response($actor->standalone_capabilities);
    }
}
