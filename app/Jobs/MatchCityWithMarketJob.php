<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MatchCityWithMarketJob.php
 */

namespace Neo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Neo\Models\City;
use Neo\Models\Market;

class MatchCityWithMarketJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected readonly int $cityID) {
    }

    public function handle(): void {
        $city = City::query()->find($this->cityID);

        if (!$city || !$city->geolocation) {
            return;
        }

        $market = Market::query()
                        ->whereRaw(DB::raw("ST_CONTAINS(area, ST_GEOMFROMTEXT('{$city->geolocation->toWKT()}'))"))
                        ->first();

        if (!$market) {
            return;
        }

        $city->market_id = $market->getKey();
        $city->save();
    }
}
