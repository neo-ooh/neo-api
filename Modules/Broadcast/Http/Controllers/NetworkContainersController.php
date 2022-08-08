<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkContainersController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Http\Requests\ListContainersRequest;
use Neo\Modules\Broadcast\Models\Network;

class NetworkContainersController extends Controller {
    public function index(ListContainersRequest $request, Network $network): Response {
        return new Response($network->containers);
    }
}
