<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WeatherRecord
 *
 * @package Neo\Models
 *
 * @property int             $id
 * @property string          $endpoint
 * @property int             $location_id
 * @property string          $locale
 * @property string          $content
 * @property Date            $created_at
 * @property Date            $updated_at
 *
 * @property WeatherLocation $location
 */
class WeatherRecord extends Model {
    protected $table = "weather_records";

    protected $fillable = [
        "endpoint",
        "location_id",
        "locale",
        "content"
    ];

    public function location(): BelongsTo {
        return $this->belongsTo(WeatherLocation::class, 'location_id');
    }
}