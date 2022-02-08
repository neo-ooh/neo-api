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
use Neo\Models\Tag;

class ActorsTagsController {
    public function sync(SyncTagsRequest $request, Actor $actor) {
        $tags = collect($request->input("tags", []));

        $tagModels = $tags->map(function (string $tag) {
            return Tag::query()->firstOrCreate(["name" => $tag]);
        });

        $actor->tags()->sync($tagModels->pluck("id"));

        return new Response($actor->tags);
    }
}
