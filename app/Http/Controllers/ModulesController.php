<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ModulesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;

class ModulesController extends Controller {
    public function status(): Response {
        return new Response(collect(config('modules-legacy'))->map(fn($m) => $m["enabled"] ?? true));
    }
}
