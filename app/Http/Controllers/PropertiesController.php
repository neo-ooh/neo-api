<?php

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Neo\Enums\Capability;
use Neo\Http\Requests\Properties\DestroyPropertyRequest;
use Neo\Http\Requests\Properties\ListPropertiesRequest;
use Neo\Http\Requests\Properties\ShowPropertyRequest;
use Neo\Http\Requests\Properties\StorePropertyRequest;
use Neo\Http\Requests\Properties\UpdateAddressRequest;
use Neo\Jobs\Odoo\PushPropertyGeolocationJob;
use Neo\Jobs\PullAddressGeolocationJob;
use Neo\Jobs\PullPropertyAddressFromBroadSignJob;
use Neo\Models\Actor;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Property;
use Neo\Models\Province;

class PropertiesController extends Controller {

    public function index(ListPropertiesRequest $request) {
        $properties = Property::all();
        $properties->load(["data", "address", "actor", "odoo.products", "odoo.products.product_type"]);
        $properties->append(["network"]);

        return $properties;
    }

    public function store(StorePropertyRequest $request) {
        // We need to make sure that the targeted actor is indeed a group
        $actorId = $request->input("actor_id");
        /** @var Actor $actor */
        $actor = Actor::find($actorId);

        if (!$actor->is_group) {
            throw new InvalidArgumentException("Only groups can be properties. $actor->name is not a group.");
        }

        // And that the group is not already a property
        if ($actor->is_property) {
            throw new InvalidArgumentException("This group is already a property");
        }

        // All good
        // Create the property
        $property           = new Property();
        $property->actor_id = $actorId;
        $property->save();
        $property->refresh();

        // Create the data and traffic records for the property
        $property->data()->create();
        $property->traffic()->create();

        // Load the address of the property
        PullPropertyAddressFromBroadSignJob::dispatch($property->actor_id);

        $property->load(["actor", "traffic", "address"]);

        if (Gate::allows(Capability::properties_edit)) {
            $property->load(["data"]);
        }

        if (Gate::allows(Capability::odoo_properties)) {
            $property->load(["odoo", "odoo.products", "odoo.products.product_type"]);
        }

        return new Response($property, 201);
    }

    public function show(ShowPropertyRequest $request, int $propertyId) {
        // Is this group a property ?
        $property = Property::query()->find($propertyId);

        if ($property) {
            $property->load(["actor", "traffic", "address"]);

            if (Gate::allows(Capability::properties_edit)) {
                $property->load(["data"]);
            }

            if (Gate::allows(Capability::odoo_properties)) {
                $property->load(["odoo", "odoo.products", "odoo.products.product_type"]);
            }

            return new Response($property);
        }


        // Since the requested actor is not a property, we'll either load all its children data in a compound, or its own.
        /** @var Actor $actor */
        $actor = Actor::query()->find($propertyId);

        if ($request->input("summed", false)) {
            $actor->append("compound_traffic");
            $actor->makeHidden("property");
            return new Response($actor);
        }

        // Is there any children bellow ?
        /** @var Collection $childGroups */
        $childGroups = $actor->selectActors()
                             ->directChildren()
                             ->where("is_group", "=", true)
                             ->orderBy("name")
                             ->get();

        if ($childGroups->isEmpty()) {
            // No group children, and not a property, return 404;
            return new Response(null, 404);
        }

        $actor->properties = $childGroups->append("compound_traffic");

        $actor->properties->makeHidden("property");

        return new Response($actor);
    }

    public function updateAddress(UpdateAddressRequest $request, Property $property) {
        /** @var Province $province */
        $province = Province::query()
                            ->where("slug", "=", $request->input("province"))
                            ->first();

        /** @var City $city */
        $city = City::query()->firstOrCreate([
            "name"        => $request->input("city"),
            "province_id" => $province->id,
        ]);

        $address          = $property->address ?? new Address();
        $address->line_1  = $request->input("line_1");
        $address->line_2  = $request->input("line_2");
        $address->city_id = $city->id;
        $address->zipcode = $request->input("zipcode");
        $address->save();

        $property->address()->associate($address);
        $property->save();

        PullAddressGeolocationJob::dispatch($address);

        if ($property->odoo) {
            PushPropertyGeolocationJob::dispatch($property->id);
        }

        return new Response($address);
    }

    public function destroy(DestroyPropertyRequest $request, Property $property) {
        $property->delete();

        return new Response(["status" => "ok"]);
    }
}
