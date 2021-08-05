<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 *
 * @package Neo\Models
 * @property string $line_1
 * @property string $line_2
 * @property int    $city_id
 * @property string $zipcode
 * @property Point  $geolocation
 * @property Date   $created_at
 * @property Date   $updated_at
 *
 * @property int    $id
 */
class Address extends Model {
    use HasFactory;
    use SpatialTrait;

    protected $table = "addresses";

    protected $primaryKey = "id";

    protected array $spatialFields = [
        "geolocation",
    ];

    protected $with = [
        "city",
        "city.province",
        "city.province.country",
        "city.market",
    ];

    public function city() {
        return $this->belongsTo(City::class, "city_id");
    }
}
