<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficSourcesController.php
 */

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
        return new Response(TrafficSource::with("settings")->get());
    }

    public function store(StoreTrafficSourceRequest $request) {
        $source = new TrafficSource();
        $source->type = $request->input("type");
        $source->name = $request->input("name");
        $source->save();

        if($source->type === "linkett") {
            $source->settings()->create(["api_key" => $request->input("api_key")]);
        }

        return new Response($source->refresh()->load("settings"), 201);
    }

    public function update(UpdateTrafficSourceRequest $request, TrafficSource $trafficSource) {
        $trafficSource->name = $request->input("name");
        $trafficSource->save();

        if($trafficSource->type === "linkett") {
            $trafficSource->settings->api_key = $request->input("api_key");
            $trafficSource->settings->save();
        }

        return new Response($trafficSource->load("settings"));
    }

    public function destroy(DestroyTrafficSourceRequest $request, TrafficSource $trafficSource) {
        $trafficSource->delete();
    }
}
