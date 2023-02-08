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
use Neo\Exceptions\BaseException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Exceptions\LibraryStorageFullException;
use Neo\Modules\Broadcast\Http\Requests\LibrariesContents\ListLibraryContentsRequest;
use Neo\Modules\Broadcast\Http\Requests\LibrariesContents\MoveContentsRequest;
use Neo\Modules\Broadcast\Models\Content;
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

    /**
     * @throws BaseException
     * @throws LibraryStorageFullException
     */
    public function move(MoveContentsRequest $request, Library $library) {
        $contents  = Content::query()->findMany($request->input("contents"));
        $layoutIds = $contents->pluck("layout_id")->unique();

        $libraryLayoutIds = $library->layouts->pluck("id")->all();

        // Are the contents layouts available in the target library ?
        if ($layoutIds->some(fn(int $layoutId) => !in_array($layoutId, $libraryLayoutIds, true))) {
            throw new BaseException("Cannot store content in a library without the content the content's layout.", "library.missing-layout");
        }

        // Does the library has a storage limit, and would moving these contents there exceeds the limit ?
        $library->loadCount("contents");
        if ($library->content_limit > 0 && $library->contents_count + $contents->count() > $library->content_limit) {
            throw new LibraryStorageFullException();
        }

        // All seems good, update the contents
        /** @var Content $content */
        foreach ($contents as $content) {
            $content->library_id = $library->getKey();
            $content->save();
        }

        return new Response(["status" => "ok"], 200);
    }
}
