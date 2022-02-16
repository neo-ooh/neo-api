<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorTagsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsTags\SyncTagsRequest;
use Neo\Models\Actor;

class ActorsTagsController {
    public function sync(SyncTagsRequest $request, Actor $actor) {
        $actor->tags()->sync($request->input("tags", []));

        return new Response($actor->tags);
    }
}