<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherPeriodsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Http\Requests\WeatherPeriod\ListPeriodsRequest;
use Neo\Models\WeatherPeriod;

class WeatherPeriodsController {
    public function index(ListPeriodsRequest $request) {
        return new Response(WeatherPeriod::query()->get());
    }

    public function store(Request $request) {
        //
    }

    public function show(WeatherPeriod $weatherPeriod) {
        //
    }

    public function update(Request $request, WeatherPeriod $weatherPeriod) {
        //
    }

    public function destroy(WeatherPeriod $weatherPeriod) {
        //
    }
}
