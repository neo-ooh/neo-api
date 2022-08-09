<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PointOfInterest.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
    use SpatialTrait;

    public bool $wktOptions = false;

    protected $table = "points_of_interest";

    protected $primaryKey = "id";

    protected array $spatialFields = [
        "position",
    ];

    protected $fillable = [
        "brand_id",
        "external_id",
        "name",
        "address"
    ];
}
