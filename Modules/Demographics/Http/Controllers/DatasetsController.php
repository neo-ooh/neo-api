<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatasetsController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\Datasets\ListDatasetsRequest;
use Neo\Modules\Demographics\Models\Dataset;

class DatasetsController extends Controller {
    public function index(ListDatasetsRequest $request) {
        $datasets = Dataset::query()->get();

        return new Response($datasets->loadPublicRelations());
    }
}
