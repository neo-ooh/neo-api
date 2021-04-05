<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WeatherLocation
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $background_selection
 * @property Date   $selection_revert_date
 * @property Date   $created_at
 * @property Date   $updated_at
 */
class WeatherLocation extends Model {
    protected $table = "weather_locations";

    protected $fillable = [
        'country',
        'province',
        'city',
        'background_selection',
        'selection_revert_date',
        'endpoint'
    ];

    protected $dates = [
        "selection_revert_date"
    ];

//	public function backgrounds() {
//    	return $this->hasMany('App\WeatherBackground', 'location');
//    }
}
