<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastKernelServiceProvider.php
 */

namespace Neo\Modules\Properties\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class PropertiesKernelServiceProvider extends ServiceProvider {
    public function register() {
    }

    public function boot() {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
        });
    }
}
