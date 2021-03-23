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
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\ActorsCapabilities\ListActorCapabilitiesRequest;
use Neo\Http\Requests\ActorsCapabilities\SyncActorCapabilitiesRequest;
use Neo\Models\Actor;
use Neo\Models\ActorCapability;
use Neo\Models\Capability;

class ActorsCapabilitiesController extends Controller {
    public function index (ListActorCapabilitiesRequest $request, Actor $actor): Response {
        $capabilities = $actor->standalone_capabilities;

        return new Response($capabilities);
    }

    public function sync (SyncActorCapabilitiesRequest $request, Actor $actor): Response {
        $capabilities = $request->validated()['capabilities'];

        // Make sure the listed capabilities are all standalone
        foreach ($capabilities as $capability) {
            if (!Capability::query()->find($capability)->standalone) {
                return new Response([
                    "code"    => "capabilities.not-assigned",
                    "message" => "User do not have capability",
                ],
                    403);
            }
        }

        // All good, add the capabilities
        $capabilitiesID = $actor->standalone_capabilities->pluck("id")->values()->toArray();

        $toAdd = array_diff($capabilities, $capabilitiesID);
        $toRemove = array_diff($capabilitiesID, $capabilities);

        foreach ($toAdd as $cID) {
            ActorCapability::create([
                "actor_id"       => $actor->getKey(),
                "capability_id" => $cID,
            ]);
        }

        if(count($toRemove) > 0) {
            $binds = implode(", ", array_fill(0, count($toRemove), "?"));

            DB::delete("DELETE FROM `actors_capabilities` WHERE `capability_id` IN ({$binds}) AND `actor_id` = ?",
                [
                    ...$toRemove,
                    $actor->getKey(),
                ]);
        }

        $actor->unsetRelations();
        return new Response($actor->standalone_capabilities);
    }
}
