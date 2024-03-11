<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AreaTypesController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\AreaTypes\ListAreaTypesRequest;
use Neo\Modules\Demographics\Models\AreaType;

class AreaTypesController extends Controller {
    public function index(ListAreaTypesRequest $request) {
        $areaTypes = AreaType::query()->orderBy("name")->get();

        return new Response($areaTypes->loadPublicRelations());
    }
}
