<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayTypesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\DisplayTypes\ListDisplayTypesRequest;
use Neo\Models\DisplayType;

class DisplayTypesController extends Controller {
    public function index(ListDisplayTypesRequest $request) {
        return new Response(DisplayType::all());
    }
}
