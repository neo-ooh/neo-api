<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LibrariesSharesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Neo\Http\Requests\LibrariesShares\DestroyLibraryShareRequest;
use Neo\Http\Requests\LibrariesShares\StoreLibraryShareRequest;
use Neo\Models\Library;

class LibrariesSharesController extends Controller
{
    /**
     * @param StoreLibraryShareRequest $request
     * @param Library $library
     *
     * @return ResponseFactory|Response
     */
    public function store(StoreLibraryShareRequest $request, Library $library)
    {
        $library->shares()->attach($request->validated()["actor_id"]);

        return new Response($library->shares);
    }

    /**
     * @param DestroyLibraryShareRequest $request
     * @param Library $library
     *
     * @return ResponseFactory|Response
     */
    public function destroy(DestroyLibraryShareRequest $request, Library $library)
    {
        $library->shares()->detach($request->validated()["actor_id"]);

        return new Response($library->shares);
    }
}
