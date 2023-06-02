<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenTypesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\ScreenTypes\DestroyScreenTypeRequest;
use Neo\Modules\Properties\Http\Requests\ScreenTypes\ListScreenTypesRequest;
use Neo\Modules\Properties\Http\Requests\ScreenTypes\ShowScreenTypeRequest;
use Neo\Modules\Properties\Http\Requests\ScreenTypes\StoreScreenTypeRequest;
use Neo\Modules\Properties\Http\Requests\ScreenTypes\UpdateScreenTypeRequest;
use Neo\Modules\Properties\Models\ScreenType;

class ScreenTypesController extends Controller {
    public function index(ListScreenTypesRequest $request) {
        return new Response(ScreenType::all()->loadPublicRelations());
    }

    public function store(StoreScreenTypeRequest $request) {
        $screenType          = new ScreenType();
        $screenType->name_en = $request->input("name_en");
        $screenType->name_fr = $request->input("name_fr");
        $screenType->save();

        return new Response($screenType, 201);
    }

    public function show(ShowScreenTypeRequest $request, ScreenType $screenType) {
        return new Response($screenType->loadPublicRelations());
    }

    public function update(UpdateScreenTypeRequest $request, ScreenType $screenType) {
        $screenType->name_en = $request->input("name_en");
        $screenType->name_fr = $request->input("name_fr");
        $screenType->save();

        return new Response($screenType->loadPublicRelations());
    }

    public function destroy(DestroyScreenTypeRequest $request, ScreenType $screenType) {
        $screenType->delete();

        return new Response(["status" => "ok"]);
    }
}
