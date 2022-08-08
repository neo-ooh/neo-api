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

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Exceptions\LibraryStorageFullException;
use Neo\Modules\Broadcast\Http\Requests\Contents\DestroyContentRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\ShowContentRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\StoreContentRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\SwapContentCreativesRequest;
use Neo\Modules\Broadcast\Http\Requests\Contents\UpdateContentRequest;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\Library;

class ContentsController extends Controller {
    /**
     * @param StoreContentRequest $request
     *
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
            $emptyContent->owner_id = $request->input("owner_id");
            $emptyContent->save();

            // Since the content already exist in the library, we don't have to check the library's content limit.
            return new Response($emptyContent->load("layout.frames"), 200);
        }

        /** @var Library $library */
        $library = Library::query()->find($request->input("library_id"));

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
        $content->owner_id   = $request->input("owner_id");
        $content->layout_id  = $request->input("layout_id");
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
        return new Response($content->withPublicRelations());
    }

    /**
     * @param UpdateContentRequest $request
     * @param Content              $content
     *
     * @return Response
     * @throws LibraryStorageFullException
     */
    public function update(UpdateContentRequest $request, Content $content): Response {
        if ($content->library_id !== $request->validated()["library_id"]) {
            /** @var Library $library */
            $library = Library::query()->find($request->validated()["library_id"]);

            // Check if the new library has enough space available
            if ($library->content_limit > 0 && $library->content_limit <= $library->contents_count) {
                throw new LibraryStorageFullException();
            }
        }

        [
            "owner_id"   => $content->owner_id,
            "library_id" => $content->library_id,
            "name"       => $content->name,
        ] = $request->validated();

        // If the user is a reviewer, it can fill additional fields
        if (Gate::allows(Capability::contents_review->value)) {
            $content->is_approved           = $request->get("is_approved", $content->is_approved);
            $content->max_schedule_duration = $request->input("max_schedule_duration", $content->max_schedule_duration);
            $content->max_schedule_count    = $request->input("max_schedule_count", $content->max_schedule_count);
        }

        $content->save();
        return new Response($content);
    }

    public function swap(SwapContentCreativesRequest $request, Content $content): Response {
        // make sure the Content can be edited
        if (!$content->is_editable) {
            return new Response([
                "code"    => "content.locked",
                "message" => "This content is locekd and cannot be edited."
            ], 400);
        }

        // Creatives are named left and right for ease of comprehension. Actual swaping could happen between any two frames as long as they have the same dimensions.
        [$leftId, $rightId] = $request->validated()["creatives"];
        /** @var Creative $left */
        $left = Creative::query()->findOrFail($leftId);
        /** @var Creative $right */
        $right = Creative::query()->findOrFail($rightId);

        // Make sure the two creatives are part of the same content
        if ($left->content_id !== $content->id || $right->content_id !== $content->id) {
            return new Response([
                "code"    => "creative.unrelated",
                "message" => "Creatives needs to be part of the same content to be swapped."
            ], 400);
        }

        // Make sure the two frames have the same dimensions so that swapping can happen
        if ($left->frame->width !== $right->frame->width || $left->frame->height !== $right->frame->height) {
            return new Response([
                "code"    => "creative.mismatch",
                "message" => "Creatives needs to have the same dimensions to be swapped."
            ], 400);
        }

        // All good, swap the creatives
        $leftFrame       = $left->frame_id;
        $left->frame_id  = $right->frame_id;
        $right->frame_id = $leftFrame;

        $left->save();
        $right->save();

        // Reload the content and return it
        $content->refresh();

        return new Response($content->withPublicRelations());
    }

    /**
     * @param DestroyContentRequest $request
     * @param Content               $content
     *
     * @return Response
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function destroy(DestroyContentRequest $request, Content $content): Response {
        $content->authorizeAccess();

        if ($content->schedules_count > 0 && $content->schedules->some(fn($schedule) => $schedule->status === 'broadcasting' || $schedule->status === 'expired')) {
            // We don't want to remove a content that has played or is currently playing
            $content->delete();
        } else {
            $content->forceDelete();
        }

        return new Response([]);
    }
}
