<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Province
 *
 * @property int $id
 * @property string $slug
 * @property int $country_id
 * @property string $name
 *
 * @property Country $country
 *
 * @package Neo\Models
 */
class Province extends Model
{
    use HasFactory;

    protected $table = "provinces";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function country() {
        return $this->belongsTo(Country::class, "country_id");
    }

    public function markets() {
        return $this->hasMany(Market::class, "province_id");
    }

    public function cities() {
        return $this->hasMany(City::class, "province_id");
    }
}