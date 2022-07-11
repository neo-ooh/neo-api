<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Param.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Neo\Models\Param
 *
 * @property string $slug
 * @property string $format
 * @property string $value
 * @property Date   $created_at
 * @property Date   $updated_at
 *
 * @mixin Builder
 */
class Param extends Model {
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
     * @var array
     */
    protected $fillable = [
        'slug',
        'format',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'approved' => 'boolean',
    ];
}
