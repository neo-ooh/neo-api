<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TagsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Models\Tag;
use Neo\Modules\Properties\Http\Requests\Tags\DestroyTagRequest;
use Neo\Modules\Properties\Http\Requests\Tags\ListTagsRequest;
use Neo\Modules\Properties\Http\Requests\Tags\StoreTagRequest;
use Neo\Modules\Properties\Http\Requests\Tags\UpdateTagRequest;

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
        $tag->name = $request->input("name");
//        $tag->color = $request->input("color");
        $tag->save();

        return new Response($tag);
    }

    public function destroy(DestroyTagRequest $request, Tag $tag) {
        $tag->delete();

        return new Response(["status" => "ok"]);
    }
}
