<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesTenantsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Neo\Http\Requests\PropertiesTenants\ListTenantsRequest;
use Neo\Http\Requests\PropertiesTenants\SyncTenantsRequest;
use Neo\Models\Property;

class PropertiesTenantsController {
    public function index(ListTenantsRequest $request, Property $property) {
        return new Response($property->tenants);
    }

    public function sync(SyncTenantsRequest $request, Property $property) {
        $property->tenants()->sync($request->input("tenants", []));

        $property->tenants_updated_at = Date::now();
        $property->save();

        return new Response($property->tenants);
    }
}
