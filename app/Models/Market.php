<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Market.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Market
 *
 * @package Neo\Models
 * @property int      $province_id
 * @property string   $name_en
 * @property string   $name_fr
 *
 * @property Province $province
 *
 * @property int      $id
 */
class Market extends Model {
    protected $table = "markets";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = ["province_id", "name_en", "name_fr"];

    public function province() {
        return $this->belongsTo(Province::class, "province_id");
    }
}
