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
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $property->refresh();

        return new Response($property->load(["actor", "traffic_data"]), 201);
    }

    public function show(ShowPropertyRequest $request, int $propertyId) {
        // Is this group a property ?
        $property = Property::query()->find($propertyId);

        if($property) {
            return new Response($property->load(["actor", "traffic_data"]));
        }

        // This group is not a property, does it has properties below it ?
        /** @var Actor $actor */
        $actor = Actor::query()->find($propertyId);
        $childrenIds = $actor->selectActors()->directChildren()->where("is_group", "=", true)->get("id")->pluck("id");

        if(count($childrenIds) > 0) {
            return new Response(Property::query()->findMany($childrenIds)->load(["actor", "traffic_data"]));
        }

        throw new HttpException(404);
    }

    public function update(UpdatePropertyRequest $request, Property $property) {
        // All good, just pass along the new value
        $property->require_traffic = $request->input("require_traffic");
        $property->traffic_start_year = $request->input("traffic_start_year");
        $property->traffic_grace_override = $request->input("traffic_grace_override");
        $property->save();

        return new Response($property->load(["actor", "traffic_data"]));
    }

    public function destroy(DestroyPropertyRequest $request, Property $property) {
        $property->delete();

        return new Response(["status" => "ok"]);
    }
}
