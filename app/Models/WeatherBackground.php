<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBackground.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class WeatherBackground
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property string $weather
 * @property string $period
 * @property int    $network_id
 * @property string $weather_location_id
 * @property string $format_id
 * @property string $path
 * @property Date   $created_at
 * @property Date   $updated_at
 */
class WeatherBackground extends Model {
    protected $table = "weather_backgrounds";

    protected $appends = ["url"];

    protected $casts = [
        "format_id"  => "integer",
        "network_id" => "integer",
    ];

    public static function boot(): void {
        parent::boot();

        static::deleting(function (WeatherBackground $background) {
            Storage::disk("public")->delete($background->path);
        });
    }

    public function getUrlAttribute(): string {
        return Storage::disk("public")->url($this->path);
    }
}
