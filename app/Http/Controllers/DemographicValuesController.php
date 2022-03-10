<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicValuesController.php
 */

namespace Neo\Http\Controllers;

use Neo\Http\Requests\DemograhicValues\StoreDemographicValuesRequest;
use Neo\Models\Property;

class DemographicValuesController {
    public function store(StoreDemographicValuesRequest $request, Property $property) {
        dd($request->input("files"));
    }
}
