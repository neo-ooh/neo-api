<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - City.php
 */

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

    protected $fillable = [
        "name",
        "market_id",
        "province_id"
    ];

    public function province() {
        return $this->belongsTo(Province::class, "province_id");
    }

    public function market() {
        return $this->belongsTo(Market::class, "market_id");
    }
}
