<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DBServiceProvider.php
 */

namespace Neo\Providers;

use Illuminate\Support\ServiceProvider;
use Neo\Models\ConnectionSettingsBroadSign;
use Neo\Models\ConnectionSettingsPiSignage;
use Neo\Models\NetworkSettingsBroadSign;
use Neo\Models\NetworkSettingsPiSignage;

class DBServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
