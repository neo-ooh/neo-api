<?php

namespace Neo\Http\Controllers;

use Neo\Http\Requests\Screenshots\DestroyScreenshotsRequest;
use Neo\Models\Screenshot;
use Response;

class ScreenshotsController extends Controller {
    public function destroy(DestroyScreenshotsRequest $request, Screenshot $screenshot) {
        $screenshot->delete();

        return new Response();
    }
}
