<?php

namespace Neo\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Neo\Http\Requests\Network\NetworkRequest;

class NetworkController extends Controller
{
    public function refresh(NetworkRequest $request) {
        Artisan::queue("network:update");
    }
}
