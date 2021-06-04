<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\News\ListRecordsRequest;
use Neo\Services\News\NewsService;

class NewsController extends Controller {
    public function index(ListRecordsRequest $request, NewsService $news)  {
        $categoryId = $request->input("category");

        return new Response($news->getRecords($categoryId));
    }
}
