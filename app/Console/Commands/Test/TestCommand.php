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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Neo\Models\Address;
use Neo\Models\Market;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
        $address = Address::query()->whereNotNull("geolocation")
                          ->whereHas("city", function (Builder $query) {
                              $query->where("market_id", "=", 45);
                          })
                          ->inRandomOrder()->first();
        dump($address->city->name);

        dump(Market::query()
                   ->whereRaw(DB::raw("ST_CONTAINS(area, ST_GEOMFROMTEXT('{$address->geolocation->toWKT()}'))"))
                   ->first()?->name_en);
    }
}
