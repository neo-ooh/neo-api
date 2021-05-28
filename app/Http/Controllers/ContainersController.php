<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContainersController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Requests\ListContainersRequest;
use Neo\Models\Container;

class ContainersController extends Controller {
    public function index(ListContainersRequest $request): Response {
        $query = Container::query();

        $query->when($request->has("network_id"), function (Builder $query) use ($request) {
            $query->where("network_id", "=", $request->input("network_id"));
        });

        return new Response($query->get());
    }
}
