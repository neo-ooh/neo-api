<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\DisplayTypesPrintsFactors\ListFactorsRequest;
use Neo\Models\DisplayTypePrintsFactors;

class DisplayTypesPrintsFactorsController extends Controller {
    public function index(ListFactorsRequest $request) {
        return new Response(DisplayTypePrintsFactors::with("network")->get());
    }
}
