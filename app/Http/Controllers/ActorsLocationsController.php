<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Enums\Capability;
use Neo\Http\Requests\ActorsLocations\ListActorLocationsRequest;
use Neo\Http\Requests\ActorsLocations\SyncActorLocationsRequest;
use Neo\Models\Actor;
use Neo\Models\Location;

class ActorsLocationsController extends Controller {
    public function index (ListActorLocationsRequest $request, Actor $actor): Response {
        if ($actor->is(Auth::user()) && Auth::user()->hasCapability(Capability::locations_edit())) {
            return new Response(Location::all());
        }

        return new Response($actor->locations);
    }

    public function sync (SyncActorLocationsRequest $request, Actor $actor): Response {
        $locations = $request->validated()['locations'];

        // All good, add the capabilities
        $actor->own_locations()->sync($locations);
        $actor->refresh();

        return new Response([
            "own_locations" => $actor->own_locations,
            "locations"     => $actor->locations,
        ]);
    }
}
