<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class City
 *
 * @property int $id
 * @property string $name
 * @property int|null $market_id
 * @property int $province_id
 *
 * @property Province $province
 * @property Market|null $market
 *
 * @package Neo\Models
 */
class City extends Model
{
    use HasFactory;

    protected $table = "cities";

    protected $primaryKey = "id";

    public $timestamps = false;

    public function province() {
        return $this->belongsTo(Province::class, "province_id");
    }

    public function market() {
        return $this->belongsTo(Market::class, "market_id");
    }
}
