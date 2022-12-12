<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LibrariesController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Exception;
use Fuse\Fuse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Libraries\DestroyLibraryRequest;
use Neo\Modules\Broadcast\Http\Requests\Libraries\ListLibrariesRequest;
use Neo\Modules\Broadcast\Http\Requests\Libraries\SearchLibrariesRequest;
use Neo\Modules\Broadcast\Http\Requests\Libraries\ShowLibraryRequest;
use Neo\Modules\Broadcast\Http\Requests\Libraries\StoreLibraryRequest;
use Neo\Modules\Broadcast\Http\Requests\Libraries\UpdateLibraryRequest;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Modules\Broadcast\Models\Library;

class LibrariesController extends Controller {
    /**
     * @param ListLibrariesRequest $request
     *
     * @return Response
     */
    public function index(ListLibrariesRequest $request): Response {
        /** @var Collection<Library> $libraries */
        $libraries = Library::query()->whereIn("owner_id", Auth::user()?->getAccessibleActors(ids: true))->get();

        if ($request->has("formats")) {
            $libraries->load("formats");
            $libraries = $libraries->filter(fn(Library $library) => $library->formats->contains(fn(Format $format) => in_array($format->getKey(), array_map('intval', $request->input('formats', [])), true)));
        }

        if ($request->has("layouts")) {
            $libraries->load("layouts");
            $libraries = $libraries->filter(fn(Library $library) => $library->layouts->contains(fn(Layout $layout) => in_array($layout->getKey(), array_map('intval', $request->input('layouts', [])), true)));
        }

        return new Response($libraries->values()->loadPublicRelations());
    }

    public function query(SearchLibrariesRequest $request): Response {
        $q = strtolower($request->input("q"));

        /** @var Collection<Library> $libraries */
        $libraries    = Library::query()->whereIn("owner_id", Auth::user()?->getAccessibleActors(ids: true))->get();
        $searchEngine = new Fuse($libraries->toArray(), [
            "keys" => [
                "name",
            ],
        ]);
        $results      = collect($searchEngine->search($q));

        return new Response($results->map(fn($result) => $result["item"]));
    }

    /**
     * @param StoreLibraryRequest $request
     *
     * @return Response
     */
    public function store(StoreLibraryRequest $request): Response {
        $library                = new Library();
        $library->name          = $request->input("name");
        $library->owner_id      = $request->input("owner_id");
        $library->advertiser_id = $request->input("advertiser_id", null);
        $library->content_limit = $request->input("content_limit", 0);
        $library->save();

        $library->formats()->sync($request->input("formats", []));

        return new Response($library->loadPublicRelations(), 201);
    }

    /**
     * @param ShowLibraryRequest $request
     * @param Library            $library
     *
     * @return Response
     */
    public function show(ShowLibraryRequest $request, Library $library): Response {
        return new Response($library->loadPublicRelations());
    }

    /**
     * @param UpdateLibraryRequest $request
     * @param Library              $library
     *
     * @return Response
     */
    public function update(UpdateLibraryRequest $request, Library $library): Response {
        $library->name          = $request->input("name");
        $library->owner_id      = $request->input("owner_id");
        $library->advertiser_id = $request->input("advertiser_id", $library->advertiser_id);
        $library->content_limit = $request->input("content_limit");

        $library->save();

        $library->formats()->sync($request->input("formats", []));

        $library->refresh();

        return new Response($library->loadPublicRelations());
    }

    /**
     * @param DestroyLibraryRequest $request
     * @param Library               $library
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(DestroyLibraryRequest $request, Library $library): Response {
        $library->delete();

        return new Response([]);
    }
}
