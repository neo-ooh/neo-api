<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Http\Requests\Provinces\ListProvincesRequest;
use Neo\Models\Country;

class ProvincesController extends Controller
{
    public function index(ListProvincesRequest $request, Country $country) {
        return new Response($country->provinces->load(["markets", "cities"]));
    }
}
