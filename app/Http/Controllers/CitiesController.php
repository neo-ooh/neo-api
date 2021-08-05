<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CitiesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Cities\StoreCityRequest;
use Neo\Models\City;
use Neo\Models\Province;

class CitiesController extends Controller {
    public function store(StoreCityRequest $request, Province $province) {
        $city = new City();
        $city->province_id = $province->id;
        $city->market_id = $request->input("market_id");
        $city->save();

        return new Response($city);
    }
}
