<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsAccessesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsAccesses\SyncActorAccessesRequest;
use Neo\Models\Actor;

class ActorsAccessesController extends Controller {
    public function sync(SyncActorAccessesRequest $request, Actor $actor) {
        $actor->sharers()->sync($request->get("actors", []));

        return new Response($actor->sharers);
    }
}
