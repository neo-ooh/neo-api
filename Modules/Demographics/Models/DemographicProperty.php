<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicProperty.php
 */

namespace Neo\Modules\Demographics\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int                          $id
 * @property-read boolean                      $is_archived
 * @property-read string                       $name
 *
 * @property-read Collection<GeographicReport> $geographic_reports
 * @property-read Collection<Extract>          $extracts
 */
class DemographicProperty extends Model {

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    /**
     * The database of the model's table.
     *
     * @var string
     */
    protected $connection = "neo_demographics";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "properties";

    public $incrementing = false;

    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        "is_archived" => "boolean",
    ];

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany<GeographicReport>
     */
    public function geographic_reports(): HasMany {
        return $this->hasMany(GeographicReport::class, "property_id", "id");
    }

    /**
     * @return HasMany<Extract>
     */
    public function extracts(): HasMany {
        return $this->hasMany(Extract::class, "property_id", "id");
    }
}
