<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Formats\ListFormatsRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\ShowFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\StoreFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\UpdateFormatRequest;
use Neo\Modules\Broadcast\Models\Format;

class FormatsController extends Controller {
    /**
     * Depending on the user capabilities, we may return all formats or a subset of the available formats
     *
     * @param ListFormatsRequest $request
     *
     * @return Response
     */
    public function index(ListFormatsRequest $request): Response {
        if (!Gate::allows(Capability::formats_edit->value)) {
            // The current actor doesn't have the capability to access format, we will only return formats he has access to from its hierarchy
            $formats = Format::query()
                             ->orderBy("name")
                             ->whereHas("display_types", function (Builder $query) {
                                 $query->whereHas("locations", function (Builder $query) {
                                     $query->whereHas("actors", function (Builder $query) {
                                         $query->where("id", "=", Auth::id());
                                     });
                                 });
                             });

            return new Response($formats);
        }

        return new Response(Format::query()->orderBy("name")->get());
    }

    public function store(StoreFormatRequest $request): Response {
        $format             = new Format();
        $format->network_id = $request->input("network_id");
        $format->name       = $request->input("name");
        $format->save();

        $format->broadcast_tags()->sync($request->input("tags"));

        return new Response($format, 201);
    }

    /**
     * @param ShowFormatRequest $request
     * @param Format            $format
     *
     * @return Response
     */
    public function show(ShowFormatRequest $request, Format $format): Response {
        return new Response($format->withPublicRelations());
    }

    /**
     * @param UpdateFormatRequest $request
     * @param Format              $format
     *
     * @return Response
     */
    public function update(UpdateFormatRequest $request, Format $format): Response {
        $format->name = $request->input("name");
        $format->save();

        $format->broadcast_tags()->sync($request->input("tags"));

        return new Response($format);
    }
}
