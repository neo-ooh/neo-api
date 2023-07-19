<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CensusSubdivision.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

/**
 * @property string       $id
 * @property integer      $census
 * @property string       $name
 * @property string       $type
 * @property string       $province
 * @property number       $landarea_sqkm
 * @property string       $dissemination_uid
 * @property MultiPolygon $geometry
 */
class CensusSubdivision extends Model {
    use HasSpatial;

    public $table = "census_subdivisions";

    public $primaryKey = "id";

    public $timestamps = false;

    public $incrementing = false;

    protected $casts = [
        "geometry" => MultiPolygon::class,
    ];

    protected $fillable = [
        "id",
        "census",
        "name",
        "type",
        "province",
        "landarea_sqkm",
        "dissemination_uid",
        "geometry",
    ];
}
