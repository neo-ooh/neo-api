<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicVariablesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\DemographicVariables\ListVariablesRequest;
use Neo\Modules\Properties\Models\DemographicVariable;

class DemographicVariablesController {
    public function index(ListVariablesRequest $request) {
        return new Response(DemographicVariable::query()->orderBy("id")->get());
    }
}
