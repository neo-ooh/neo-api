<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\DisplayTypes\ListDisplayTypesRequest;
use Neo\Models\DisplayType;

class DisplayTypesController extends Controller {
    public function index(ListDisplayTypesRequest $request) {
        return new Response(DisplayType::all());
    }
}
