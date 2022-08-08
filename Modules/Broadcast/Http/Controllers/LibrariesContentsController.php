<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LibrariesContentsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\LibrariesContents\ListLibraryContentsRequest;
use Neo\Modules\Broadcast\Models\Library;

class LibrariesContentsController extends Controller {
    /**
     * @param ListLibraryContentsRequest $request
     * @param Library                    $library
     * @return Response
     */
    public function index(ListLibraryContentsRequest $request, Library $library): Response {
        return new Response($library->contents);
    }
}
