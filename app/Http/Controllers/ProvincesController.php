<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProvincesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Provinces\ListProvincesRequest;
use Neo\Models\Country;
use Neo\Models\Province;

class ProvincesController extends Controller {
    public function index(ListProvincesRequest $request, Country $country) {
        return new Response($country->provinces->load(["markets"]));
    }

    public function show(ListProvincesRequest $request, Country $country, Province $province) {
        return new Response($province->load(["country", "markets", "cities"]));
    }
}
