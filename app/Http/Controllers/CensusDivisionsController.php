<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CensusDivisionsController.php
 */

namespace Neo\Http\Controllers;

use Fuse\Fuse;
use Illuminate\Http\Response;
use Neo\Http\Requests\CensusDivisions\QueryCensusDivisionsRequest;
use Neo\Models\CensusDivision;

class CensusDivisionsController extends Controller {
    public function index(QueryCensusDivisionsRequest $request) {
        $subdivisions = CensusDivision::query()
                                      ->select(["id", "census", "name", "type", "province", "landarea_sqkm", "dissemination_uid"])
                                      ->get();
        $searchEngine = new Fuse($subdivisions->toArray(), [
            "keys" => [
                "name",
            ],
        ]);
        $results      = collect($searchEngine->search($request->input("query"), ["limit" => 25]));
        return new Response($results->map(fn(array $result) => $result["item"]));
    }

    public function show(CensusDivision $censusDivision) {
        return $censusDivision;
    }
}
