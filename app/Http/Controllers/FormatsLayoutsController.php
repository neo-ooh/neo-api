<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\FormatsLayouts\DestroyLayoutRequest;
use Neo\Http\Requests\FormatsLayouts\StoreLayoutRequest;
use Neo\Http\Requests\FormatsLayouts\UpdateLayoutRequest;
use Neo\Models\FormatLayout;

class FormatsLayoutsController extends Controller {
    public function store(StoreLayoutRequest $request): Response {
        $layout                = new FormatLayout();
        $layout->format_id     = $request->get("format_id");
        $layout->name          = $request->get("name");
        $layout->is_fullscreen = $request->get("is_fullscreen");
        $layout->trigger_id    = $request->get("trigger_id");
        $layout->separation_id = $request->get("separation_id");
        $layout->save();

        return new Response($layout->load("frames"), 201);
    }

    public function update(UpdateLayoutRequest $request, FormatLayout $layout): Response {
        $layout->name = $request->get("name");
        $layout->is_fullscreen = $request->get("is_fullscreen");
        $layout->trigger_id    = $request->get("trigger_id");
        $layout->separation_id = $request->get("separation_id");
        $layout->save();

        return new Response($layout);
    }

    public function destroy(DestroyLayoutRequest $request, FormatLayout $layout): Response {
        $layout->frames->each(fn($frame) => $frame->delete());
        $layout->delete();

        return new Response();
    }
}
