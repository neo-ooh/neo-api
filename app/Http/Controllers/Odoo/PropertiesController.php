<?php
/*
* Copyright 2020 (c) Neo-OOH - All Rights Reserved
* Unauthorized copying of this file, via any medium is strictly prohibited
* Proprietary and confidential
* Written by Valentin Dufois <vdufois@neo-ooh.com>
*
* @neo/api - PropertiesController.php
*/

namespace Neo\Http\Controllers\Odoo;

use Illuminate\Http\Response;
use InvalidArgumentException;
use Neo\Http\Controllers\Controller;
use Neo\Http\Requests\Odoo\Properties\DestroyPropertyRequest;
use Neo\Http\Requests\Odoo\Properties\StorePropertyRequest;
use Neo\Jobs\Odoo\SyncPropertyDataJob;
use Neo\Jobs\PullPropertyAddressFromOdooJob;
use Neo\Models\Odoo\Property as OdooProperty;
use Neo\Services\Odoo\Models\Property;
use Neo\Services\Odoo\OdooConfig;

class PropertiesController extends Controller {
    public function store(StorePropertyRequest $request): Response {
        $propertyId = $request->input("property_id");
        $odooId     = $request->input("odoo_id");

// Make sure this property is not already associated with an Odoo property
        if (OdooProperty::query()->where("property_id", "=", $propertyId)->exists()) {
            throw new \http\Exception\InvalidArgumentException("Connect property is already associated with an Odoo Property.");
        }

// Check the odoo property is not already associated with a Connect property
        /** @var Property|null $existing */
        $existing = OdooProperty::query()
                                ->with(["property", "property.actor"])
                                ->where("odoo_id", "=", $odooId)->first();

        if ($existing !== null) {
            throw new InvalidArgumentException("Odoo Property is already associated with {$existing->property->actor->name} [{$existing->property->actor->path_names}].");
        }

// We are good, we just have to pull info from odoo about the property, and store it
        $config = OdooConfig::fromConfig();
        $client = $config->getClient();

        $odooPropertyDist = Property::get($client, $odooId);

        if ($odooPropertyDist === null) {
            throw new InvalidArgumentException("Invalid Odoo Id");
        }

        $odooProperty                = new OdooProperty();
        $odooProperty->property_id   = $propertyId;
        $odooProperty->odoo_id       = $odooId;
        $odooProperty->internal_name = $odooPropertyDist->name;
        $odooProperty->save();

// Trigger a sync of the property products
        PullPropertyAddressFromOdooJob::dispatchSync($propertyId);
        SyncPropertyDataJob::dispatchSync($odooProperty->property_id, $client);

        return new Response($odooProperty, 201);
    }

    /**
     * @param DestroyPropertyRequest $request
     * @param OdooProperty           $property
     * @return Response
     */
    public function destroy(DestroyPropertyRequest $request, OdooProperty $property): Response {
        $property->delete();

        return new Response([]);
    }

}
