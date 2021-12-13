<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class WeatherBackground
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property string $weather
 * @property string $period_id
 * @property string $day_part
 * @property int    $network_id
 * @property string $weather_location_id
 * @property string $format_id
 * @property string $path
 * @property Date   $created_at
 * @property Date   $updated_at
 */
class WeatherBackground extends Model {
    use HasFactory;

    protected $table = "weather_backgrounds";

    protected $appends = ["url"];

    protected $casts = [
        "format_id"  => "integer",
        "network_id" => "integer",
    ];

    public static function boot(): void {
        parent::boot();

        static::deleting(function (WeatherBackground $background) {
            Storage::delete($background->path);
        });
    }

    public function getUrlAttribute(): string {
        return Storage::url($this->path);
    }
}
