<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkFieldsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\NetworkFields\ListFieldsRequest;
use Neo\Http\Requests\NetworkFields\UpdateFieldsRequest;
use Neo\Models\Network;

class NetworkFieldsController {
    public function index(ListFieldsRequest $request, Network $network) {
        return new Response($network->properties_fields);
    }

    public function update(UpdateFieldsRequest $request, Network $network) {
        $entries = collect($request->input("fields"))->mapWithKeys(fn($field) => [$field["field_id"] => $field["order"]]);
        $network->properties_fields()->sync($entries);
    }
}
