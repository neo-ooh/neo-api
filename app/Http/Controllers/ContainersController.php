<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Models\Container;

class ContainersController extends Controller {
    public function index(): Response {
        return new Response(Container::all());
    }
}
