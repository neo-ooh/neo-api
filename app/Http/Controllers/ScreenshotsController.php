<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Screenshots\DestroyScreenshotsRequest;
use Neo\Models\Screenshot;

class ScreenshotsController extends Controller {
    public function destroy(DestroyScreenshotsRequest $request, Screenshot $screenshot) {
        $screenshot->delete();

        return new Response([$screenshot->id]);
    }
}
