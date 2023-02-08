<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers\Odoo;

use Illuminate\Http\Response;
use InvalidArgumentException;
use Neo\Http\Controllers\Controller;
use Neo\Jobs\Odoo\SynchronizePropertyData;
use Neo\Jobs\PullPropertyAddressFromOdooJob;
use Neo\Modules\Properties\Http\Requests\Odoo\Properties\DestroyPropertyRequest;
use Neo\Modules\Properties\Http\Requests\Odoo\Properties\StorePropertyRequest;
use Neo\Modules\Properties\Models\Odoo\Property as OdooProperty;
use Neo\Services\Odoo\Models\Property;
use Neo\Services\Odoo\OdooConfig;

class PropertiesController extends Controller {
    public function store(StorePropertyRequest $request): Response {
        $propertyId = $request->input("property_id");
        $odooId     = $request->input("odoo_id");

// Make sure this property is not already associated with an Odoo property
        if (OdooProperty::query()->where("property_id", "=", $propertyId)->exists()) {
            throw new InvalidArgumentException("Connect property is already associated with an Odoo Property.");
        }

// Check the odoo property is not already associated with a Connect property
        /** @var OdooProperty|null $existing */
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
        SynchronizePropertyData::dispatchSync($odooProperty->getKey(), $client);

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
