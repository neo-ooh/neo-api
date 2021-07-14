<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Http\Requests\TrafficSources\DestroyTrafficSourceRequest;
use Neo\Http\Requests\TrafficSources\ListTrafficSourcesRequest;
use Neo\Http\Requests\TrafficSources\StoreTrafficSourceRequest;
use Neo\Http\Requests\TrafficSources\UpdateTrafficSourceRequest;
use Neo\Models\TrafficSource;

class TrafficSourcesController extends Controller
{
    public function index(ListTrafficSourcesRequest $request) {
        return new Response(TrafficSource::all());
    }

    public function store(StoreTrafficSourceRequest $request) {
        $source = new TrafficSource();
        $source->type = $request->input("type");
        $source->name = $request->input("name");
        $source->save();

        if($source->type === "linkett") {
            $source->settings()->create(["api_key" => $request->input("api_key")]);
        }

        return new Response($source->refresh(), 201);
    }

    public function update(UpdateTrafficSourceRequest $request, TrafficSource $trafficSource) {
        $trafficSource->name = $request->input("name");

        if($trafficSource->type === "linkett") {
            $trafficSource->settings->api_key = $request->input("api_key");
            $trafficSource->settings->save();
        }

        return new Response($trafficSource);
    }

    public function destroy(DestroyTrafficSourceRequest $request, TrafficSource $trafficSource) {
        $trafficSource->delete();
    }
}
