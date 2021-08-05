<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Market
 *
 * @property int $id
 * @property int $province_id
 * @property string $name_en
 * @property string $name_fr
 *
 * @property Province $province
 *
 * @package Neo\Models
 */
class Market extends Model
{
    use HasFactory;

    protected $table = "markets";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = ["province_id", "name_en", "name_fr"];

    public function province() {
        return $this->belongsTo(Province::class, "province_id");
    }
}
