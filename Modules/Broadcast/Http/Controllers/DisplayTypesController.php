<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
use Neo\Modules\Broadcast\Http\Requests\DisplayTypes\ShowDisplayTypeRequest;
use Neo\Modules\Broadcast\Models\DisplayType;
use Neo\Modules\Broadcast\Models\Network;

class DisplayTypesController extends Controller {
    public function index(ListDisplayTypesRequest $request) {
        if ($request->has("network_id")) {
            /** @var Network $network */
            $network      = Network::query()->findOrFail($request->input("network_id"));
            $displayTypes = DisplayType::query()->where("connection_id", "=", $network->connection_id)->orderBy("name")->get();
        } else {
            $displayTypes = DisplayType::query()->orderBy("name")->get();
        }

        return new Response($displayTypes->loadPublicRelations());
    }

    public function show(ShowDisplayTypeRequest $request, DisplayType $displayType) {
        return new Response($displayType->loadPublicRelations());
    }
}
