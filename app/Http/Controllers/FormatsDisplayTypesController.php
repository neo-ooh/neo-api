<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\FormatsDisplayTypes\SyncFormatDisplayTypesRequest;
use Neo\Models\Format;

class FormatsDisplayTypesController extends Controller {
    public function sync(SyncFormatDisplayTypesRequest $request, Format $format) {
        $format->display_types()->sync($request->validated()["display_types"]);

        return new Response($format->display_types);
    }
}
