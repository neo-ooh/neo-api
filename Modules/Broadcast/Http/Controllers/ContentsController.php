<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContentsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Exceptions\LibraryStorageFullException;
use Neo\Modules\Broadcast\Http\Requests\Contents\DestroyContentRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\ListContentsByIdsRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\ShowContentRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\StoreContentRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\UpdateContentRequest;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Library;
use Neo\Modules\Broadcast\Models\Schedule;

class ContentsController extends Controller {
    public function byIds(ListContentsByIdsRequest $request) {
        $contents = Content::query()->findMany($request->input("ids"));

        $contents->loadPublicRelations();

        return new Response($contents);
    }

    /**
     * @param StoreContentRequest $request
     * @return Response
     * @throws LibraryStorageFullException
     */
    public function store(StoreContentRequest $request): Response {
        // We want to prevent creating new empty content in a library if an empty one is already there.
        // Check if there is already an empty content
        /** @var Content|null $emptyContent */
        $emptyContent = Content::query()
                               ->where("library_id", "=", $request->input("library_id"))
                               ->where("layout_id", "=", $request->input("layout_id"))
                               ->doesntHave("creatives")
                               ->first();

        if ($emptyContent) {
            // We have an already existing empty content, reassign it to the proper user, and return that
            $emptyContent->name       = "";
            $emptyContent->owner_id   = Auth::id();
            $emptyContent->created_at = Date::now();
            $emptyContent->save();

            // Since the content already exist in the library, we don't have to check the library's content limit.
            return new Response($emptyContent->load("layout.frames"), 200);
        }

        /** @var Library $library */
        $library = Library::query()->findOrFail($request->input("library_id"));

        // Check if the library has enough space available
        if ($library->content_limit !== 0 && $library->contents_count > $library->content_limit) {
            throw new LibraryStorageFullException();
        }

        // Validate that the layout requested for the content is allowed in this library
        $layoutId = $request->input("layout_id");
        if (!$library->layouts->contains($layoutId)) {
            throw new InvalidArgumentException("Layout #$layoutId is not allowed in this library");
        }

        $content             = new Content();
        $content->owner_id   = Auth::id();
        $content->layout_id  = $layoutId;
        $content->library_id = $library->getKey();
        $content->save();
        $content->refresh();

        return new Response($content->load("layout.frames"), 201);
    }

    /**
     * @param ShowContentRequest $request
     * @param Content            $content
     *
     * @return Response
     */
    public function show(ShowContentRequest $request, Content $content): Response {
        return new Response($content->loadPublicRelations());
    }

    /**
     * @param UpdateContentRequest $request
     * @param Library              $library
     * @param Content              $content
     *
     * @return Response
     * @throws LibraryStorageFullException
     */
    public function update(UpdateContentRequest $request, Library $library, Content $content): Response {
        if ($library->getKey() !== $request->validated()["library_id"]) {
            // Check if the new library has enough space available
            if ($library->content_limit > 0 && $library->content_limit <= $library->contents_count) {
                throw new LibraryStorageFullException();
            }

            $content->library_id = $request->input("library_id");
        }

        $content->owner_id = $request->input("owner_id");
        $content->name     = $request->input("name");

        // If the user is a reviewer, it can fill additional fields
        if (Gate::allows(Capability::contents_review->value)) {
            $content->is_approved           = $request->get("is_approved", $content->is_approved);
            $content->max_schedule_duration = $request->input("max_schedule_duration", $content->max_schedule_duration);
            $content->max_schedule_count    = $request->input("max_schedule_count", $content->max_schedule_count);
        }

        if (Gate::allows(Capability::contents_tags->value)) {
            $content->broadcast_tags()->sync($request->input("tags"));
        }

        $content->save();

        $content->promote();

        return new Response($content->loadPublicRelations());
    }

    /**
     * @param DestroyContentRequest $request
     * @param Library               $library
     * @param Content               $content
     *
     * @return Response
     * @throws AuthorizationException
     * @noinspection PhpUnusedParameterInspection
     */
    public function destroy(DestroyContentRequest $request, Library $library, Content $content): Response {
        if ($content->schedules_count > 0 && $content->schedules->some(fn(Schedule $schedule) => $schedule->isApproved() && !$schedule->trashed())) {
            // We don't want to remove a content that has approved schedules
            $content->delete();
        } else {
            $content->forceDelete();
        }

        return new Response([]);
    }
}
