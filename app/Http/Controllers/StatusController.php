<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StatusController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StatusController extends Controller {
    public function getStatus() {
        return new Response([
            "maintenanceEnabled" => app()->isDownForMaintenance(),
            "maintenanceBypass"  => Gate::allows(Capability::dev_tools->value),
        ]);
    }
}
