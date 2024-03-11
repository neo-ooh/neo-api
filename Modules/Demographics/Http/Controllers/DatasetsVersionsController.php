<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatasetsVersionsController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\DatasetsVersions\ListDatasetsVersionsRequest;
use Neo\Modules\Demographics\Http\Requests\DatasetsVersions\UpdateDatasetVersionRequest;
use Neo\Modules\Demographics\Models\DatasetVersion;

class DatasetsVersionsController extends Controller {
    public function index(ListDatasetsVersionsRequest $request) {
        $datasetsVersions = DatasetVersion::query()->get();

        return new Response($datasetsVersions->loadPublicRelations());
    }

    public function update(UpdateDatasetVersionRequest $request, DatasetVersion $datasetVersion) {
        $datasetVersion->is_primary = $request->input("is_primary");
        $datasetVersion->is_archived = $request->input("is_archived");
        $datasetVersion->order = $request->input("order");
        $datasetVersion->save();

        return new Response($datasetVersion->loadPublicRelations());
    }
}
