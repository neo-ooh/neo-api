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
use Neo\Http\Requests\LibrariesShares\DestroyLibraryShareRequest;
use Neo\Http\Requests\LibrariesShares\StoreLibraryShareRequest;
use Neo\Modules\Broadcast\Models\Library;

class LibrariesSharesController extends Controller {
    /**
     * @param StoreLibraryShareRequest              $request
     * @param \Neo\Modules\Broadcast\Models\Library $library
     *
     * @return Response
     */
    public function store(StoreLibraryShareRequest $request, Library $library) {
        $library->shares()->attach($request->validated()["actor_id"]);

        return new Response($library->shares);
    }

    /**
     * @param DestroyLibraryShareRequest            $request
     * @param \Neo\Modules\Broadcast\Models\Library $library
     *
     * @return Response
     */
    public function destroy(DestroyLibraryShareRequest $request, Library $library) {
        $library->shares()->detach($request->validated()["actor_id"]);

        return new Response($library->shares);
    }
}
