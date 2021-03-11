<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - FormatsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Requests\Formats\ListFormatsRequest;
use Neo\Http\Requests\Formats\ShowFormatRequest;
use Neo\Http\Requests\Formats\StoreFormatRequest;
use Neo\Http\Requests\Formats\UpdateFormatRequest;
use Neo\Models\Actor;
use Neo\Models\Format;

class FormatsController extends Controller
{
    /**
     * @param ListFormatsRequest $request
     *
     * @return ResponseFactory|Response
     */
    public function index(ListFormatsRequest $request)
    {
        if($request->has("actor")) {
            // An actor is specified, we only return formats accessible by the user
            $formats = Actor::query()->findOrFail($request->query("actor"))->getLocations()->pluck("display_type.formats")->flatten()->unique("id")->values();

            if($request->has('enabled')) {
                $formats = $formats->filter(fn($format) => $format->is_enabled)->values();
            }
        } else {
            $formats = Format::query()
                             ->when($request->has("enabled"),
                                 fn(Builder $query) => $query->where("is_enabled", "=", (bool)$request->get("enabled")))
                             ->with("display_types")
                             ->get();
        }

        return new Response($formats);
    }

    public function store(StoreFormatRequest $request): Response {
        $format = new Format();
        [
            "name" => $format->name,
            "is_enabled" => $format->is_enabled,
        ] = $request->validated();
        $format->save();

        return new Response($format, 201);
    }

    /**
     * @param ShowFormatRequest $request
     * @param Format $format
     *
     * @return ResponseFactory|Response
     * @noinspection PhpUnusedParameterInspection
     */
    public function show(ShowFormatRequest $request, Format $format)
    {
        return new Response($format->load("display_types"));
    }

    /**
     * @param UpdateFormatRequest $request
     * @param Format $format
     *
     * @return ResponseFactory|Response
     */
    public function update(UpdateFormatRequest $request, Format $format)
    {
        [
            "name" => $format->name,
            "is_enabled" => $format->is_enabled,
        ] = $request->validated();
        $format->save();

        return new Response($format);
    }
}
