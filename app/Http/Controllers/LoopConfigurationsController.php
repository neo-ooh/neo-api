<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoopConfigurationsController.php
 */

namespace Neo\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Neo\Http\Requests\LoopConfigurations\DeleteLoopConfigurationRequest;
use Neo\Http\Requests\LoopConfigurations\ShowLoopConfigurationRequest;
use Neo\Http\Requests\LoopConfigurations\StoreLoopConfigurationRequest;
use Neo\Http\Requests\LoopConfigurations\UpdateLoopConfigurationRequest;
use Neo\Modules\Broadcast\Models\LoopConfiguration;

class LoopConfigurationsController {
    public function index() {
        $loops = LoopConfiguration::query()->orderBy("name")->get();

        return new Response($loops);
    }

    public function store(StoreLoopConfigurationRequest $request) {
        $loopConfiguration = new LoopConfiguration([
            "name"           => $request->input("name"),
            "loop_length_ms" => $request->input("loop_length_ms"),
            "spot_length_ms" => $request->input("spot_length_ms"),
            "reserved_spots" => $request->input("reserved_spots"),
            "start_date"     => Carbon::parse($request->input("start_date"))->setYear(2000),
            "end_date"       => Carbon::parse($request->input("end_date"))->setYear(2000),
        ]);

        $loopConfiguration->save();

        return new Response($loopConfiguration, 201);
    }

    public function show(ShowLoopConfigurationRequest $request, $loopConfiguration) {
        return new Response($loopConfiguration);
    }

    public function update(UpdateLoopConfigurationRequest $request, LoopConfiguration $loopConfiguration) {
        $loopConfiguration->name           = $request->input("name");
        $loopConfiguration->loop_length_ms = $request->input("loop_length_ms");
        $loopConfiguration->spot_length_ms = $request->input("spot_length_ms");
        $loopConfiguration->reserved_spots = $request->input("reserved_spots");
        $loopConfiguration->start_date     = Carbon::parse($request->input("start_date"))->setYear(2000);
        $loopConfiguration->end_date       = Carbon::parse($request->input("end_date"))->setYear(2000);

        $loopConfiguration->save();
        $loopConfiguration->refresh();

        return new Response($loopConfiguration);
    }

    public function destroy(DeleteLoopConfigurationRequest $request, LoopConfiguration $loopConfiguration) {
        $loopConfiguration->delete();

        return new Response(["status" => "ok"]);
    }
}
