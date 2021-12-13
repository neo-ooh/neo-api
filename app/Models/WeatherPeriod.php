<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherPeriod.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherPeriod extends Model {
    protected $table = "weather_periods";

    protected $dates = [
        "start_date",
        "end_date"
    ];


    public static function boot(): void {
        parent::boot();

        static::deleting(function (WeatherPeriod $period) {
            $period->backgrounds->each(fn(WeatherBackground $background) => $background->delete());
        });
    }

    public function backgrounds() {
        return $this->hasMany(WeatherBackground::class, "period_id", "id");
    }
}
