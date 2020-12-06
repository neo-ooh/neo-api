<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - BroadSignServiceProvider.php
 */

namespace Neo\BroadSign;

use Illuminate\Support\ServiceProvider;

class BroadSignServiceProvider extends ServiceProvider {
    public function register () {
        parent::register();

        $this->app->bind('broadsign', fn () => new BroadSign());
        $this->app->alias('BroadSign', BroadSignFacade::class);
    }
}
