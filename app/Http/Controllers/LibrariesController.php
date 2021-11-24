<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LibrariesController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Http\Controllers;

use Exception;
use Fuse\Fuse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Libraries\DestroyLibraryRequest;
use Neo\Http\Requests\Libraries\ListLibrariesRequest;
use Neo\Http\Requests\Libraries\SearchLibrariesRequest;
use Neo\Http\Requests\Libraries\StoreLibraryRequest;
use Neo\Http\Requests\Libraries\UpdateLibraryRequest;
use Neo\Models\Library;

class LibrariesController extends Controller {
    /**
     * @param ListLibrariesRequest $request
     *
     * @return Response
     */
    public function index(ListLibrariesRequest $request) {
        $libraries = Auth::user()->getLibraries();

        if ($request->has("withContent")) {
            $libraries->load("contents", "contents.layout");
        }

        return new Response($libraries);
    }

    public function query(SearchLibrariesRequest $request) {
        $q = strtolower($request->input("q"));

        $libraries    = Auth::user()->getLibraries()->load("contents", "contents.layout");
        $searchEngine = new Fuse($libraries->toArray(), [
            "keys" => [
                "name"
            ]
        ]);

        return new Response(collect($searchEngine->search($q))->map(fn($result) => $result->get("item")));
    }

    /**
     * @param StoreLibraryRequest $request
     *
     * @return Response
     */
    public function store(StoreLibraryRequest $request) {
        // Passed data have been cleared by the FormRequest
        $library = new Library();
        [
            "name"     => $library->name,
            "owner_id" => $library->owner_id,
            "capacity" => $library->content_limit,
        ] = $request->validated();
        $library->save();

        return new Response($library->load(["owner"]), 201);
    }

    /**
     * @param Library $library
     *
     * @return Response
     */
    public function show(Library $library) {
        // User authorization has been cleared by the FormRequest
        $library->append("available_formats");
        return new Response($library->load(["contents", "contents.layout", "shares"]));
    }

    /**
     * @param UpdateLibraryRequest $request
     * @param Library              $library
     *
     * @return Response
     */
    public function update(UpdateLibraryRequest $request, Library $library): Response {
        // Passed data have been cleared by the FormRequest
        [
            "name"          => $library->name,
            "owner_id"      => $library->owner_id,
            "content_limit" => $library->content_limit,
        ] = $request->validated();
        $library->save();
        $library->refresh();

        return new Response($library->load(["contents", "shares"]));
    }

    /**
     * @param DestroyLibraryRequest $request
     * @param Library               $library
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(DestroyLibraryRequest $request, Library $library): Response {
        // User authorization has been cleared by the FormRequest
        // The library takes care of destroying all its related resources
        $library->delete();

        return new Response([]);
    }

    /**
     * List contents in the library
     *
     * @param Library $library
     *
     * @return Response
     */
    public function contents(Library $library): Response {
        return new Response($library->contents);
    }
}
