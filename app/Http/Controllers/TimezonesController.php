<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TimezonesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Timezones\ListTimezonesRequest;

class TimezonesController extends Controller {
    public function index(ListTimezonesRequest $request) {
        return new Response(timezone_identifiers_list());
    }
}
