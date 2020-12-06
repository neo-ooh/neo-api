<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ContainersController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Models\Container;

class ContainersController extends Controller {
    public function index(): Response {
        return new Response(Container::all());
    }
}
