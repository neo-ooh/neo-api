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
 * @property int $id
 * @property string $weather
 * @property string $period
 * @property string $weather_location_id
 * @property string $format_id
 * @property string $path
 * @property Date $created_at
 * @property Date $updated_at
 */
class WeatherBackground extends Model
{
    use HasFactory;

    protected $table = "weather_backgrounds";

    protected $appends = ["url"];

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