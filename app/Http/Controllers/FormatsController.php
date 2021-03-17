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

use Egulias\EmailValidator\Exception\LocalOrReservedDomain;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
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
     * @return ResponseFactory|Response
     */
    public function index(ListFormatsRequest $request) {
        if ($request->has("actor")) {
            // An actor is specified, we only return formats accessible by the user
            $formats = Actor::query()
                            ->findOrFail($request->query("actor"))
                            ->getLocations()
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
                             ->get();
        }

        return new Response($formats);
    }

    public function query(QueryFormatsRequest $request) {
        // we list locations matching the query terms and only keep their formats
        if ($request->has("network")) {
            $network   = Network::coerce($request->validated()["network"])->value;
            $locations = Actor::find(Param::find($network)->value)->getLocations(true, false, true, true);

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
            $locations = collect(array_map(fn($network) => Actor::find(Param::find(Network::coerce($network)))
                                                        ->getLocations(true, false, true, true), Network::getValues()))->flatten();
        }

        // Now that we have ou locations, extract the formats
        $formats = $locations->pluck("display_type.formats")
                             ->flatten()
                             ->unique("id")
                             ->values();

        return new Response($formats);
    }

    public function store(StoreFormatRequest $request): Response {
        $format = new Format();
        [
            "name"       => $format->name,
            "is_enabled" => $format->is_enabled,
        ] = $request->validated();
        $format->save();

        return new Response($format, 201);
    }

    /**
     * @param ShowFormatRequest $request
     * @param Format            $format
     *
     * @return ResponseFactory|Response
     * @noinspection PhpUnusedParameterInspection
     */
    public function show(ShowFormatRequest $request, Format $format) {
        return new Response($format->load("display_types"));
    }

    /**
     * @param UpdateFormatRequest $request
     * @param Format              $format
     *
     * @return ResponseFactory|Response
     */
    public function update(UpdateFormatRequest $request, Format $format) {
        [
            "name"       => $format->name,
            "is_enabled" => $format->is_enabled,
        ] = $request->validated();
        $format->save();

        return new Response($format);
    }
}
