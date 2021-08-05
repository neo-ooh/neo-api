<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MarketsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Markets\StoreMarketRequest;
use Neo\Http\Requests\Markets\UpdateMarketRequest;
use Neo\Models\Country;
use Neo\Models\Market;
use Neo\Models\Province;

class MarketsController extends Controller {
    public function store(StoreMarketRequest $request, Country $country, Province $province) {
        $market = new Market();
        $market->name_fr = $request->input("name_fr");
        $market->name_en = $request->input("name_en");
        $market->province_id = $province->id;

        $market->save();

        return new Response($market, 201);
    }

    public function update(UpdateMarketRequest $request, Market $market) {
        $market->name_fr = $request->input("name_fr");
        $market->name_en = $request->input("name_en");
        $market->save();

        return new Response($market);
    }
}
