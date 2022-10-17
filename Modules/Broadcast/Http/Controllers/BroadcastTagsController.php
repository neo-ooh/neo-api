<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastTagsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Enums\BroadcastTagScope;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\DeleteBroadcastTagRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\ListBroadcastTagsByIdRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\ListBroadcastTagsRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\ShowBroadcastTagsRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\StoreBroadcastTagRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastTags\UpdateBroadcastTagRequest;
use Neo\Modules\Broadcast\Models\BroadcastTag;

class BroadcastTagsController extends Controller {
    public function index(ListBroadcastTagsRequest $request): Response {
        /** @var Collection<BroadcastTag> $broadcastTags */
        $broadcastTags = BroadcastTag::query()
                                     ->when($request->filled("scope"), function (Builder $query) use ($request) {
                                         $query->whereRaw('FIND_IN_SET(?, scope)', [$request->enum("scope", BroadcastTagScope::class)->value]);
                                     })->when($request->filled("types"), function (Builder $query) use ($request) {
                $query->whereIn("type", $request->input("types"));
            })->get();

        return new Response($broadcastTags->loadPublicRelations());
    }

    public function store(StoreBroadcastTagRequest $request): Response {
        $broadcastTag          = new BroadcastTag();
        $broadcastTag->type    = $request->enum("type", BroadcastTagType::class);
        $broadcastTag->name_en = $request->input("name_en");
        $broadcastTag->name_fr = $request->input("name_fr");
        $broadcastTag->scope   = array_map(static fn(string $scope) => BroadcastTagScope::from($scope), $request->input("scope", []));
        $broadcastTag->save();

        return new Response($broadcastTag, 201);
    }

    public function show(ShowBroadcastTagsRequest $request, BroadcastTag $broadcastTag): Response {
        return new Response($broadcastTag->loadPublicRelations());
    }

    public function by_id(ListBroadcastTagsByIdRequest $request): Response {
        /** @var Collection<BroadcastTag> $broadcastTags */
        $broadcastTags = BroadcastTag::withTrashed()->whereIn("id", $request->input("ids", []))->get();

        return new Response($broadcastTags->each(fn(BroadcastTag $tag) => $tag->loadPublicRelations()));
    }

    public function update(UpdateBroadcastTagRequest $request, BroadcastTag $broadcastTag): Response {
        $broadcastTag->name_en = $request->input("name_en");
        $broadcastTag->name_fr = $request->input("name_fr");
        $broadcastTag->scope   = array_map(static fn(string $scope) => BroadcastTagScope::from($scope), $request->input("scope", []));
        $broadcastTag->save();

        return new Response($broadcastTag->loadPublicRelations());
    }

    public function destroy(DeleteBroadcastTagRequest $request, BroadcastTag $broadcastTag): Response {
        $broadcastTag->delete();

        return new Response(["status" => "ok"]);
    }
}
