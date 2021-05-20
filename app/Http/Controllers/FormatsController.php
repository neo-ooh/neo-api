<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Enums\Network;
use Neo\Http\Requests\Formats\ListFormatsRequest;
use Neo\Http\Requests\Formats\QueryFormatsRequest;
use Neo\Http\Requests\Formats\ShowFormatRequest;
use Neo\Http\Requests\Formats\StoreFormatRequest;
use Neo\Http\Requests\Formats\UpdateFormatRequest;
use Neo\Models\Actor;
use Neo\Models\Format;
use Neo\Models\Param;

class FormatsController extends Controller {
    /**
     * @param ListFormatsRequest $request
     *
     * @return Response
     */
    public function index(ListFormatsRequest $request) {
        if ($request->has("actor")) {
            // An actor is specified, we only return formats accessible by the user
            $formats = Actor::query()
                            ->findOrFail($request->query("actor"))
                            ->getLocations(true, true, true, true)
                            ->pluck("display_type.formats")
                            ->flatten()
                            ->unique("id")
                            ->values();

            if ($request->has('enabled')) {
                $formats = $formats->filter(fn($format) => $format->is_enabled)->values();
            }
        } else {
            $formats = Format::query()
                             ->when($request->has("enabled"),
                                 fn(Builder $query) => $query->where("is_enabled", "=", (bool)$request->get("enabled")))
                             ->with("display_types")
                             ->orderBy("name")
                             ->get();
        }

        return new Response($formats);
    }

    public function query(QueryFormatsRequest $request) {
        // we list locations matching the query terms and only keep their formats
        if ($request->has("network")) {
            $network   = Network::coerce($request->validated()["network"])->value;
            $locations = Actor::query()->find(Param::query()->find($network)->value)->getLocations(true, false, true, true);

            if ($request->has("province")) {
                $province  = $request->validated()["province"];
                $locations = $locations->filter(fn($location) => $location->province === $province);
            }

            if ($request->has("city")) {
                $city      = $request->validated()["city"];
                $locations = $locations->filter(fn($location) => $location->city === $city);
            }
        } else {
            // If no network is specified, we load all locations in each network
            $locations = collect(array_map(static fn($network) => Actor::query()->find(Param::query()
                                                                                            ->find(Network::coerce($network)))
                                                                       ->getLocations(true, false, true, true), Network::getValues()))->flatten();
        }

        // Now that we have ou locations, extract the formats
        $formats = $locations->pluck("display_type.formats")
                             ->flatten()
                             ->unique("id")
                             ->sortBy("name")
                             ->values();

        return new Response($formats);
    }

    public function store(StoreFormatRequest $request): Response {
        $format = new Format();
        [
            "name"       => $format->name,
            "slug"       => $format->slug,
            "is_enabled" => $format->is_enabled,
        ] = $request->validated();
        $format->save();

        return new Response($format, 201);
    }

    /**
     * @param ShowFormatRequest $request
     * @param Format            $format
     *
     * @return Response
     * @noinspection PhpUnusedParameterInspection
     */
    public function show(ShowFormatRequest $request, Format $format) {
        return new Response($format->load("display_types"));
    }

    /**
     * @param UpdateFormatRequest $request
     * @param Format              $format
     *
     * @return Response
     */
    public function update(UpdateFormatRequest $request, Format $format) {
        [
            "name"       => $format->name,
            "slug"       => $format->slug,
            "is_enabled" => $format->is_enabled,
        ] = $request->validated();
        $format->save();

        return new Response($format);
    }
}
