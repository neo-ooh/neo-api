<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TagsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Tags\DestroyTagRequest;
use Neo\Http\Requests\Tags\ListTagsRequest;
use Neo\Http\Requests\Tags\StoreTagRequest;
use Neo\Http\Requests\Tags\UpdateTagRequest;
use Neo\Models\Tag;

class TagsController {
    public function index(ListTagsRequest $request) {
        return new Response(Tag::query()->orderBy("name")->get());
    }

    public function store(StoreTagRequest $request) {
        $tag = new Tag(["name" => $request->input("name")]);
        $tag->save();

        return new Response($tag, 201);
    }

    public function update(UpdateTagRequest $request, Tag $tag) {
        $tag->name  = $request->input("name");
        $tag->color = $request->input("color");
        $tag->save();

        return new Response($tag);
    }

    public function destroy(DestroyTagRequest $request, Tag $tag) {
        $tag->delete();

        return new Response(["status" => "ok"]);
    }
}
