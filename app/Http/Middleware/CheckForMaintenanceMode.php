<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CheckForMaintenanceMode.php
 */

namespace Neo\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class CheckForMaintenanceMode extends Middleware {
    protected $except = [
        "/_status",
    ];

    public function handle($request, Closure $next) {
        if ($this->app->isDownForMaintenance() && Gate::allows(Capability::dev_tools->value)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
