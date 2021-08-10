<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use InvalidArgumentException;
use Neo\Http\Requests\Properties\DestroyPropertyRequest;
use Neo\Http\Requests\Properties\ShowPropertyRequest;
use Neo\Http\Requests\Properties\StorePropertyRequest;
use Neo\Http\Requests\Properties\UpdateAddressRequest;
use Neo\Http\Requests\Properties\UpdatePropertyRequest;
use Neo\Models\Actor;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Property;
use Neo\Models\PropertyTrafficSettings;
use Neo\Models\Province;
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
        // Create the property
        $property           = new Property();
        $property->actor_id = $actorId;
        $property->save();
        $property->refresh();

        // Create the traffic settings
        $property->traffic()->create();

        return new Response($property->load(["actor", "traffic"]), 201);
    }

    public function show(ShowPropertyRequest $request, int $propertyId) {
        // Is this group a property ?
        $property = Property::query()->find($propertyId);

        if($property) {
            return new Response($property->load(["actor", "traffic", "address"]));
        }

        // Since the requested actor is not a property, we'll either load all its children data in a compound, or its own.
        /** @var Actor $actor */
        $actor = Actor::query()->find($propertyId);

        if($request->input("summed", false)) {
            $actor->append("compound_traffic");
            $actor->makeHidden("property");
            return new Response($actor);
        }

        $actor->properties = $actor->selectActors()
                                   ->directChildren()
                                   ->where("is_group", "=", true)
                                   ->orderBy("name")
                                   ->get()
                                   ->append("compound_traffic");

        $actor->properties->makeHidden("property");

        return new Response($actor);
    }

    public function update(UpdatePropertyRequest $request, Property $property) {
        // All good, just pass along the new value
        $property->require_traffic = $request->input("require_traffic");
        $property->traffic_start_year = $request->input("traffic_start_year");
        $property->traffic_grace_override = $request->input("traffic_grace_override");
        $property->save();

        return new Response($property->load(["actor", "traffic"]));
    }

    public function updateAddress(UpdateAddressRequest $request, Property $property) {
        /** @var Province $province */
        $province = Province::query()
                            ->where("slug", "=", $request->input("province"))
                            ->first();

        /** @var City $city */
        $city = City::query()->firstOrCreate([
            "name" => $request->input("city"),
            "province_id" => $province->id,
        ]);

        $address = $property->address ?? new Address();
        $address->line_1 = $request->input("line_1");
        $address->line_2 = $request->input("line_2");
        $address->city_id = $city->id;
        $address->zipcode = $request->input("zipcode");
        $address->save();

        return new Response($address);
    }

    public function destroy(DestroyPropertyRequest $request, Property $property) {
        $property->delete();

        return new Response(["status" => "ok"]);
    }
}
