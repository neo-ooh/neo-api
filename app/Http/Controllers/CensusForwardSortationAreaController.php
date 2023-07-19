<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CensusForwardSortationAreaController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\CensusForwardSortationAreas\QueryCensusForwardSortationAreasRequest;
use Neo\Models\CensusForwardSortationArea;

class CensusForwardSortationAreaController extends Controller {
    public function index(QueryCensusForwardSortationAreasRequest $request) {
        $fsas = CensusForwardSortationArea::query()
                                          ->select(["id", "census", "province", "landarea_sqkm", "dissemination_uid"])
                                          ->where("id", "like", $request->input("query") . "%")
                                          ->get();
        return new Response($fsas);
    }

    public function show(CensusForwardSortationArea $censusForwardSortationArea) {
        return $censusForwardSortationArea;
    }
}
