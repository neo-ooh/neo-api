<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Advertiser.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int    $id
 * @property string $name
 * @property int    $external_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Advertiser extends Model {
    protected $table = "advertisers";

    protected $primaryKey = "id";

    protected $fillable = [
        "name",
        "external_id"
    ];
}
