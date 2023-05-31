<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyTypesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\PropertyTypes\DestroyPropertyTypeRequest;
use Neo\Modules\Properties\Http\Requests\PropertyTypes\ListPropertyTypesRequest;
use Neo\Modules\Properties\Http\Requests\PropertyTypes\ShowPropertyTypeRequest;
use Neo\Modules\Properties\Http\Requests\PropertyTypes\StorePropertyTypeRequest;
use Neo\Modules\Properties\Http\Requests\PropertyTypes\UpdatePropertyTypeRequest;
use Neo\Modules\Properties\Models\PropertyType;

class PropertyTypesController extends Controller {
    public function index(ListPropertyTypesRequest $request) {
        return new Response(PropertyType::all()->loadPublicRelations());
    }

    public function store(StorePropertyTypeRequest $request) {
        $propertyType          = new PropertyType();
        $propertyType->name_en = $request->input("name_en");
        $propertyType->name_fr = $request->input("name_fr");
        $propertyType->save();

        return new Response($propertyType, 201);
    }

    public function show(ShowPropertyTypeRequest $request, PropertyType $propertyType) {
        return new Response($propertyType->loadPublicRelations());
    }

    public function update(UpdatePropertyTypeRequest $request, PropertyType $propertyType) {
        $propertyType->name_en = $request->input("name_en");
        $propertyType->name_fr = $request->input("name_fr");
        $propertyType->save();

        return new Response($propertyType->loadPublicRelations());
    }

    public function destroy(DestroyPropertyTypeRequest $request, PropertyType $propertyType) {
        $propertyType->delete();

        return new Response(["status" => "ok"]);
    }
}
