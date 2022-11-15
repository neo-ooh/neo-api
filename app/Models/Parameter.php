<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Parameter.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Neo\Models\Utils\ParamValueCaster;

/**
 * Neo\Models\Parameter
 *
 * @property string                $slug
 * @property string                $format
 * @property \Neo\Enums\Capability $capability
 * @property string                $value
 * @property Date                  $created_at
 * @property Date                  $updated_at
 *
 * @mixin Builder
 */
class Parameter extends Model {
    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    public $incrementing = false;
    /**
     * The connection name for the model.
     *
     * @var string
     */

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'parameters';
    protected $primaryKey = "slug";
    protected $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'slug',
        'format',
        'capability',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "approved"   => "boolean",
        "capability" => \Neo\Enums\Capability::class,
        "value"      => ParamValueCaster::class,
    ];
}
