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
 * @property int    $id
 * @property string $line_1
 * @property string $line_2
 * @property int    $city_id
 * @property City   $city
 * @property string $zipcode
 * @property Point  $geolocation
 * @property Date   $created_at
 * @property Date   $updated_at
 *
 * @property string    $string_representation Human-readable version of the address
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

    protected $appends = [
        "string_representation"
    ];

    public function city() {
        return $this->belongsTo(City::class, "city_id");
    }

    public function getStringRepresentationAttribute(): string {
        $str = $this->line_1;
        if($this->line_2 && strlen($this->line_2) > 0) {
            $str .= ", $this->line_2";
        }

        $str .= ", {$this->city->name} {$this->city->province->slug} {$this->zipcode}";
        $str .= ", {$this->city->province->country->name}";

        return $str;
    }
}
