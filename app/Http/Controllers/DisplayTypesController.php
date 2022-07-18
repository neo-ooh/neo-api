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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Requests\DisplayTypes\ListDisplayTypesPerNetworkRequest;
use Neo\Http\Requests\DisplayTypes\ListDisplayTypesRequest;
use Neo\Http\Requests\DisplayTypes\UpdateDisplayTypeRequest;
use Neo\Modules\Broadcast\Models\DisplayType;
use Neo\Modules\Broadcast\Models\Network;

class DisplayTypesController extends Controller {
    public function index(ListDisplayTypesRequest $request) {
        return new Response(DisplayType::all());
    }

    public function byNetwork(ListDisplayTypesPerNetworkRequest $request, Network $network) {
        $displayTypes = DisplayType::query()->whereHas('locations', function (Builder $query) use ($network) {
            $query->where("network_id", "=", $network->id);
        })->orderBy("name")->get();

        return new Response($displayTypes);
    }

    public function update(UpdateDisplayTypeRequest $request, DisplayType $displayType) {
        $displayType->name = $request->input("name");
        $displayType->save();

        return new Response($displayType);
    }
}
