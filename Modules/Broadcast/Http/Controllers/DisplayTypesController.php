<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayTypesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\DisplayTypes\ListDisplayTypesRequest;
use Neo\Modules\Broadcast\Models\DisplayType;
use Neo\Modules\Broadcast\Models\Network;

class DisplayTypesController extends Controller {
    public function index(ListDisplayTypesRequest $request) {
        /** @var Network $network */
        $network = Network::query()->findOrFail($request->input("network_id"));
        return new Response(DisplayType::query()->where("connection_id", "=", $network->connection_id)->get());
    }
}
