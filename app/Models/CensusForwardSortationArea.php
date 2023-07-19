<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CensusForwardSortationArea.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;

/**
 * @property string       $id
 * @property integer      $census
 * @property string       $province
 * @property float        $landarea_sqkm
 * @property string       $dissemination_uid
 * @property MultiPolygon $geometry
 */
class CensusForwardSortationArea extends Model {
    public $timestamps = false;

    public $incrementing = false;

    protected $table = "census_forward_sortation_areas";

    protected $casts = [
        "geometry" => MultiPolygon::class,
    ];

    protected $fillable = [
        "census",
        "province",
        "landarea_sqkm",
        "dissemination_uid",
        "geometry",
    ];
}
