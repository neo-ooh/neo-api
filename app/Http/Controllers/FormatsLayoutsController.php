<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\FormatsLayouts\DestroyLayoutRequest;
use Neo\Http\Requests\FormatsLayouts\StoreLayoutRequest;
use Neo\Http\Requests\FormatsLayouts\UpdateLayoutRequest;
use Neo\Models\FormatLayout;

class FormatsLayoutsController extends Controller {
    public function store(StoreLayoutRequest $request): Response {
        $layout = new FormatLayout();
        [
            "format_id" => $layout->format_id,
            "name" => $layout->name,
            "is_fullscreen" => $layout->is_fullscreen,
        ] = $request->validated();

        $layout->save();

        return new Response($layout->load("frames"), 201);
    }

    public function update(UpdateLayoutRequest $request, FormatLayout $layout): Response {
        [
            "name" => $layout->name,
            "is_fullscreen" => $layout->is_fullscreen,
        ] = $request->validated();

        $layout->save();

        return new Response($layout);
    }

    public function destroy(DestroyLayoutRequest $request, FormatLayout $layout): Response {
        $layout->frames->each(fn($frame) => $frame->delete());
        $layout->delete();

        return new Response();
    }
}
