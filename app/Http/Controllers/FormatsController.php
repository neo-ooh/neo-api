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
use Neo\Http\Requests\Formats\UpdateFormatRequest;
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
        return new Response(Format::query()
                                  ->when($request->has("enabled"),
                                      fn(Builder $query) => $query->where("is_enabled", "=", $request->get("enabled")))
                                  ->get());
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
        return new Response($format);
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
            "slug" => $format->slug,
            "is_enabled" => $format->is_enabled,
        ] = $request->validated();
        $format->save();

        return new Response($format);
    }
}
