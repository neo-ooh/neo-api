<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ContentsController.php
 */

namespace Neo\Http\Controllers;

use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Exceptions\LibraryStorageFullException;
use Neo\Http\Requests\Contents\DestroyContentRequest;
use Neo\Http\Requests\Contents\StoreContentRequest;
use Neo\Http\Requests\Contents\UpdateContentRequest;
use Neo\Models\Content;
use Neo\Models\Library;

class ContentsController extends Controller
{
    /**
     * @param StoreContentRequest $request
     *
     * @return ResponseFactory|Response
     * @throws LibraryStorageFullException
     */
    public function store(StoreContentRequest $request)
    {
        /** @var Library $library */
        $library = Library::query()->find($request->validated()["library_id"]);

        // Check if the library has enough space available
        if ($library->content_limit !== 0 && $library->contents_count > $library->content_limit) {
            throw new LibraryStorageFullException();
        }

        $content = new Content();
        [
            "owner_id" => $content->owner_id,
            "library_id" => $content->library_id,
            "layout_id" => $content->layout_id,
        ] = $request->validated();

        $content->save();
        $content->refresh();

        return new Response($content->load("layout.frames"), 201);
    }

    /**
     * @param Content $content
     *
     * @return ResponseFactory|Response
     */
    public function show(Content $content)
    {
        return new Response($content->load([
            "creatives",
            "schedules",
            "schedules.campaign:id,name",
            "layout",
            "layout.format",
            "library:id,name"]));
    }

    /**
     * @param UpdateContentRequest $request
     * @param Content $content
     *
     * @return ResponseFactory|Response
     * @throws LibraryStorageFullException
     */
    public function update(UpdateContentRequest $request, Content $content)
    {
        if ($content->library_id !== $request->validated()["library_id"]) {
            /** @var Library $library */
            $library = Library::query()->find($request->validated()["library_id"]);

            // Check if the new library has enough space available
            if ($library->content_limit <= $library->contents_count) {
                throw new LibraryStorageFullException();
            }
        }

        [
            "owner_id" => $content->owner_id,
            "library_id" => $content->library_id,
            "name" => $content->name,
        ] = $request->validated();

        // If the user is a reviewer, it can fill additional fields
        if (Gate::allows(Capability::contents_review)) {
            $content->is_approved = $request->get("is_approved", $content->is_approved);
            $content->scheduling_duration = $request->get("scheduling_duration", $content->scheduling_duration);
            $content->scheduling_times = $request->get("scheduling_times", $content->scheduling_times);
        }

        $content->save();
        return new Response($content->load(["owner",
            "creatives",
            "schedules",
            "schedules.campaign",
            "layout",
            "library"]));
    }

    /**
     * @param DestroyContentRequest $request
     * @param Content $content
     *
     * @return ResponseFactory|Response
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function destroy(DestroyContentRequest $request, Content $content)
    {
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
