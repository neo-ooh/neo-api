<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Capability.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Neo\Enums\Capability as CapabilityEnum;

/**
 * Neo\Models\Capability
 *
 * @property int    id
 * @property string slug
 * @property string service
 * @property mixed  default
 * @property bool   standalone
 *
 * @mixin Builder
 */
class Capability extends Model {
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'capabilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "slug",
        "standalone",
        "service",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'standalone' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Custom mechanisms
    |--------------------------------------------------------------------------
    */

    public static function bySlug (CapabilityEnum $capability) {
        return static::query()->where("slug", "=", $capability->value)->first();
    }
}
