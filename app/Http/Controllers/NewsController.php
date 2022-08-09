<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\News\ListRecordsRequest;
use Neo\Services\News\NewsService;

class NewsController extends Controller {
    public function index(ListRecordsRequest $request, NewsService $news): Response {
        $categoryId = $request->input("category");

        return new Response($news->getRecords($categoryId));
    }
}
