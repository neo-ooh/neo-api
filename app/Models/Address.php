<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Adresse
 *
 * @property int $id
 * @property string $line_1
 * @property string $line_2
 * @property int $city_id
 * @property string $zipcode
 * @property Date $created_at
 * @property Date $updated_at
 *
 * @package Neo\Models
 */
class Address extends Model
{
    use HasFactory;

    protected $table = "addresses";

    protected $primaryKey = "id";

    public function city() {
        return $this->belongsTo(City::class, "city_id");
    }
}
