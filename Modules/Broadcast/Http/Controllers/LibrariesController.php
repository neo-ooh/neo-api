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
use Neo\Modules\Broadcast\Models\Library;

class LibrariesController extends Controller {
    /**
     * @param ListLibrariesRequest $request
     *
     * @return Response
     */
    public function index(ListLibrariesRequest $request): Response {
        /** @noinspection NullPointerExceptionInspection We are necessarily logged in if we passed the route and request checks */
        /** @var Collection<Library> $libraries */
        $libraries = Auth::user()->getLibraries();

        return new Response($libraries->each->withPublicRelations());
    }

    public function query(SearchLibrariesRequest $request): Response {
        $q = strtolower($request->input("q"));

        /** @noinspection NullPointerExceptionInspection We are necessarily logged in if we passed the route and request checks */
        $libraries    = Auth::user()->getLibraries();
        $searchEngine = new Fuse($libraries->toArray(), [
            "keys" => [
                "name"
            ]
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
        $library->content_limit = $request->input("content_limit", 0);
        $library->save();

        $library->formats()->sync($request->input("formats"));

        return new Response($library->withPublicRelations(), 201);
    }

    /**
     * @param ShowLibraryRequest $request
     * @param Library            $library
     *
     * @return Response
     */
    public function show(ShowLibraryRequest $request, Library $library): Response {
        return new Response($library->withPublicRelations());
    }

    /**
     * @param UpdateLibraryRequest $request
     * @param Library              $library
     *
     * @return Response
     */
    public function update(UpdateLibraryRequest $request, Library $library): Response {
        $library->name           = $request->input("name");
        $library->owner_id       = $request->input("owner_id");
        $library->content_limit  = $request->input("content_limit");
        $library->hidden_formats = $request->input("hidden_formats", []);

        $library->save();

        $library->formats()->sync($request->input("formats"));

        $library->refresh();

        return new Response($library->withPublicRelations());
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
