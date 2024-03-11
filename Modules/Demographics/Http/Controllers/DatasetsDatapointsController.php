<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatasetsDatapointsController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\DatasetsDatapoints\ListDatapointsRequest;
use Neo\Modules\Demographics\Http\Requests\DatasetsDatapoints\ShowDatapointRequest;
use Neo\Modules\Demographics\Http\Requests\DatasetsDatapoints\UpdateDatapointRequest;
use Neo\Modules\Demographics\Models\DatasetDatapoint;

class DatasetsDatapointsController extends Controller {
    public function index(ListDatapointsRequest $request) {
        $datapoints = DatasetDatapoint::query()
            ->where("dataset_version_id", "=", $request->input("dataset_version_id"))
            ->orderBy("code")
            ->get();

        return new Response($datapoints->loadPublicRelations());
    }

    public function show(ShowDatapointRequest $request, DatasetDatapoint $datapoint) {
        return new Response($datapoint->loadPublicRelations());
    }

    public function update(UpdateDatapointRequest $request, DatasetDatapoint $datapoint) {
        $datapoint->label_en = $request->input("label_en");
        $datapoint->label_fr = $request->input("label_fr");
        $datapoint->save();

        return new Response($datapoint->loadPublicRelations());
    }
}
