<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Neo\Http\Requests\Network\NetworkRequest;

class NetworkController extends Controller
{
    public function refresh(NetworkRequest $request) {
        Artisan::queue("network:update");
    }
}
