<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsLocationsController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsLocations\ListActorLocationsRequest;
use Neo\Http\Requests\ActorsLocations\SyncActorLocationsRequest;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Format;

class ActorsLocationsController extends Controller {
    public function index(ListActorLocationsRequest $request, Actor $actor): Response {
        $locations = $actor->getLocations(true, true, false);

        // Should we scope by network ?
        if ($request->has("network_id")) {
            $locations = $locations->where("network_id", "=", $request->input("network_id"));
        }

        // Should we scope by format ?
        if ($request->has("format_id")) {
            $displayTypes = Format::query()->find($request->input("format_id"))->display_types->pluck("id");
            $locations    = $locations->whereIn("display_type_id", $displayTypes);
        }

        return new Response($locations->loadPublicRelations());
    }

    public function sync(SyncActorLocationsRequest $request, Actor $actor): Response {
        $locations = $request->validated()['locations'];

        // All good, add the capabilities
        $actor->own_locations()->sync($locations);
        $actor->refresh();

        return new Response([
            "own_locations" => $actor->getLocations(true, false, false),
            "locations"     => $actor->getLocations(),
        ]);
    }
}
