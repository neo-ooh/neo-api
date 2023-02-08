<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesTenantsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\PropertiesTenants\ListTenantsRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesTenants\RemoveTenantRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesTenants\SyncTenantsRequest;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\Property;

class PropertiesTenantsController {
    public function index(ListTenantsRequest $request, Property $property): Response {
        return new Response($property->tenants);
    }

    public function sync(SyncTenantsRequest $request, Property $property): Response {
        $property->tenants()->sync($request->input("tenants", []));

        $property->last_review_at = Carbon::now();
        $property->save();

        return new Response($property->tenants);
    }

    public function remove(RemoveTenantRequest $request, Property $property, Brand $brand): Response {
        $property->tenants()->detach($brand->id);

        return new Response();
    }
}
