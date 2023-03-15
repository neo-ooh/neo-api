<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Modules\Properties\Models\Product;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
        $product  = Product::query()->find(3136);
        $geocoder = app('geocoder')->using("geonames");
        dump($geocoder->reverse($product->property->address->geolocation->getLat(), $product->property->address->geolocation->getLng()));
    }
}
