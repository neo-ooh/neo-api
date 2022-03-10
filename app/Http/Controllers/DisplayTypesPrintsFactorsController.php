<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayTypesPrintsFactorsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\DisplayTypesPrintsFactors\ListFactorsRequest;
use Neo\Http\Requests\DisplayUnitsPrintsFactors\StoreFactorsRequest;
use Neo\Http\Requests\DisplayUnitsPrintsFactors\UpdateFactorsRequest;
use Neo\Models\DisplayTypePrintsFactors;

class DisplayTypesPrintsFactorsController extends Controller {
    public function index(ListFactorsRequest $request) {
        return new Response(DisplayTypePrintsFactors::with(["network", "displayTypes"])->get());
    }

    public function store(StoreFactorsRequest $request) {
        $factors = new DisplayTypePrintsFactors();
        $factors->network_id = $request->input("network_id");
        $factors->start_month = $request->input("start_month");
        $factors->end_month = $request->input("end_month");
        $factors->loop_length = $request->input("loop_length");
        $factors->product_exposure = $request->input("product_exposure");
        $factors->exposure_length = $request->input("exposure_length");
        $factors->save();

        $factors->displayTypes()->attach($request->input("display_types"));

        return new Response($factors->load(["network", "displayTypes"]), 201);
    }

    public function update(UpdateFactorsRequest $request, DisplayTypePrintsFactors $factors) {
        $factors->start_month = $request->input("start_month");
        $factors->end_month = $request->input("end_month");
        $factors->loop_length = $request->input("loop_length");
        $factors->product_exposure = $request->input("product_exposure");
        $factors->exposure_length = $request->input("exposure_length");
        $factors->save();

        $factors->displayTypes()->sync($request->input("display_types"));

        return new Response($factors->load(["network", "displayTypes"]));
    }
}
