<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PointOfInterest.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;

/**\
 * @property int    $id
 * @property int    $brand_id
 * @property string $external_id
 * @property string $name
 * @property string $address
 * @property Point  $position
 *
 * @property Date   $created_at
 * @property Date   $updated_at
 *
 * @mixin Builder<PointOfInterest>
 */
class PointOfInterest extends Model {
    protected $table = "points_of_interest";

    protected $primaryKey = "id";

    protected $casts = [
        "position" => Point::class,
    ];

    protected $fillable = [
        "brand_id",
        "external_id",
        "name",
        "address",
    ];
}
