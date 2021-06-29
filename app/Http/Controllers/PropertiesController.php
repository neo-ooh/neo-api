<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use InvalidArgumentException;
use Neo\Http\Requests\Properties\DestroyPropertyRequest;
use Neo\Http\Requests\Properties\ShowPropertyRequest;
use Neo\Http\Requests\Properties\StorePropertyRequest;
use Neo\Http\Requests\Properties\UpdatePropertyRequest;
use Neo\Models\Actor;
use Neo\Models\Property;

class PropertiesController extends Controller {
    public function store(StorePropertyRequest $request) {
        // We need to make sure that the targeted actor is indeed a group
        $actorId = $request->input("actor_id");
        $actor   = Actor::find($actorId);

        if (!$actor->is_group) {
            throw new InvalidArgumentException("Only groups can be properties. $actor->name is not a group.");
        }

        // And that the group is not already a property
        if($actor->is_property) {
            throw new InvalidArgumentException("This group is already a property");
        }

        // All good
        $property           = new Property();
        $property->actor_id = $actorId;
        $property->save();

        return new Response($property, 201);
    }

    public function show(ShowPropertyRequest $request, Property $property) {
        return new Response($property->load("traffic_data"));
    }

    public function update(UpdatePropertyRequest $request, Property $property) {
        // All good, just pass along the new value
        $property->require_traffic = $request->input("require_traffic");
        $property->traffic_start_year = $request->input("traffic_start_year");
        $property->traffic_grace_override = $request->input("traffic_grace_override");
        $property->save();

        return new Response($property);
    }

    public function destroy(DestroyPropertyRequest $request, Property $property) {
        $property->delete();

        return new Response(["status" => "ok"]);
    }
}
