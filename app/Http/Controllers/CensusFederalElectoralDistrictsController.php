<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CensusFederalElectoralDistrictsController.php
 */

namespace Neo\Http\Controllers;

use Fuse\Fuse;
use Illuminate\Http\Response;
use Neo\Http\Requests\CensusFederalElectoralDistricts\QueryCensusFederalElectoralDistrictsRequest;
use Neo\Models\CensusFederalElectoralDistrict;

class CensusFederalElectoralDistrictsController extends Controller {
    public function index(QueryCensusFederalElectoralDistrictsRequest $request) {
        $districts    = CensusFederalElectoralDistrict::query()
                                                      ->select(["id", "census", "name_en", "name_fr", "province", "landarea_sqkm", "dissemination_uid"])
                                                      ->get();
        $searchEngine = new Fuse($districts->toArray(), [
            "keys" => [
                "name_en", "name_fr",
            ],
        ]);
        $results      = collect($searchEngine->search($request->input("query"), ["limit" => 25]));
        return new Response($results->map(fn(array $result) => $result["item"]));
    }

    public function show(CensusFederalElectoralDistrict $censusFederalElectoralDistrict) {
        return $censusFederalElectoralDistrict;
    }
}
