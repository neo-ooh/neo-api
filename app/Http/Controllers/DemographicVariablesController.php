<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicVariablesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\DemographicVariables\ListVariablesRequest;
use Neo\Models\DemographicVariable;

class DemographicVariablesController {
    public function index(ListVariablesRequest $request) {
        return new Response(DemographicVariable::query()->orderBy("id")->get());
    }
}
