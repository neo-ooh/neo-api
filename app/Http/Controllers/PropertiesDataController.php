<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesDataController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\PropertiesData\UpdatePropertyDataRequest;
use Neo\Models\Property;

class PropertiesDataController extends Controller {
    public function update(UpdatePropertyDataRequest $request, Property $property) {
        $data = $property->data;

        $data->website     = $request->input("website");
        $data->description_en     = $request->input("description_en");
        $data->description_fr     = $request->input("description_fr");
        $data->stores_count       = $request->input("stores_count");
        $data->visit_length       = $request->input("visit_length");
        $data->average_income     = $request->input("average_income");
        $data->is_downtown        = $request->input("is_downtown");
        $data->data_source        = $request->input("data_source");
        $data->market_population  = $request->input("market_population");
        $data->gross_area         = $request->input("gross_area");
        $data->spending_per_visit = $request->input("spending_per_visit");
        $data->save();

        return new Response($data);
    }
}
