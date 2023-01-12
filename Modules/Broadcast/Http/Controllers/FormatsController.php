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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Formats\CloneFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\DestroyFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\ListFormatsByIdsRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\ListFormatsRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\ShowFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\StoreFormatRequest;
use Neo\Modules\Broadcast\Http\Requests\Formats\UpdateFormatRequest;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\LoopConfiguration;

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
            $accessibleActors = Auth::user()?->getAccessibleActors() ?? new Collection();

            $formats = Format::query()
                             ->orderBy("name")
                             ->whereHas("display_types", function (Builder $query) use ($accessibleActors) {
                                 $query->whereHas("locations", function (Builder $query) use ($accessibleActors) {
                                     $query->whereHas("actors", function (Builder $query) use ($accessibleActors) {
                                         $query->where("id", "in", $accessibleActors->pluck("id"));
                                     });
                                 });
                             });

            return new Response($formats->loadPublicRelations());
        }

        return new Response(Format::query()->orderBy("name")->get()->loadPublicRelations());
    }

    public function byIds(ListFormatsByIdsRequest $request): Response {
        $formats = Format::query()->findMany($request->input("ids"));
        return new Response($formats->loadPublicRelations());
    }

    public function store(StoreFormatRequest $request): Response {
        $format                 = new Format();
        $format->network_id     = $request->input("network_id");
        $format->name           = $request->input("name");
        $format->content_length = $request->input("content_length");
        $format->save();

        $format->broadcast_tags()->sync($request->input("tags"));

        return new Response($format, 201);
    }

    public function clone(CloneFormatRequest $request, Format $format): Response {
        $clone                 = new Format();
        $clone->network_id     = $request->input("network_id");
        $clone->name           = $request->input("name");
        $clone->content_length = $format->content_length;
        $clone->save();

        $clone->display_types()->sync($format->display_types);
        $clone->layouts()->sync($format->layouts);
        $clone->broadcast_tags()->sync($format->broadcast_tags);

        foreach ($format->loop_configurations as $loop_configuration) {
            $clonedLoop = new LoopConfiguration($loop_configuration->getAttributes());
            unset($clonedLoop->id);
            $clonedLoop->save();

            $clone->loop_configurations()->attach($clonedLoop);
        }

        return new Response($clone);
    }

    /**
     * @param ShowFormatRequest $request
     * @param Format            $format
     *
     * @return Response
     */
    public function show(ShowFormatRequest $request, Format $format): Response {
        return new Response($format->loadPublicRelations());
    }

    /**
     * @param UpdateFormatRequest $request
     * @param Format              $format
     *
     * @return Response
     */
    public function update(UpdateFormatRequest $request, Format $format): Response {
        $format->name           = $request->input("name");
        $format->content_length = $request->input("content_length");
        $format->save();

        $format->broadcast_tags()->sync($request->input("tags"));

        // Detach loop configuration that don't match the format anymore if the content length changed
        $format->loop_configurations()->where("spot_length_ms", "<>", "spot_length_ms")->detach();

        return new Response($format->loadPublicRelations());
    }

    /**
     * @param DestroyFormatRequest $request
     * @param Format               $format
     *
     * @return Response
     */
    public function destroy(DestroyFormatRequest $request, Format $format): Response {
        $format->delete();

        return new Response($format);
    }
}
