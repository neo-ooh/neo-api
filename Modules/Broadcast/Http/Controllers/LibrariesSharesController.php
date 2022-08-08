<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LibrariesSharesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\LibrariesShares\DestroyLibraryShareRequest;
use Neo\Modules\Broadcast\Http\Requests\LibrariesShares\ListLibrarySharesRequest;
use Neo\Modules\Broadcast\Http\Requests\LibrariesShares\StoreLibraryShareRequest;
use Neo\Modules\Broadcast\Models\Library;

class LibrariesSharesController extends Controller {
    public function index(ListLibrarySharesRequest $request, Library $library): Response {
        return new Response($library->shares);
    }

    /**
     * @param StoreLibraryShareRequest $request
     * @param Library                  $library
     *
     * @return Response
     */
    public function store(StoreLibraryShareRequest $request, Library $library): Response {
        $library->shares()->attach($request->input("actor_id"));

        return new Response($library->shares);
    }

    /**
     * @param DestroyLibraryShareRequest $request
     * @param Library                    $library
     *
     * @return Response
     */
    public function destroy(DestroyLibraryShareRequest $request, Library $library): Response {
        $library->shares()->detach($request->validated("actor_id"));

        return new Response($library->shares);
    }
}
