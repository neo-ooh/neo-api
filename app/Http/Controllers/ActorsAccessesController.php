<?php

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
