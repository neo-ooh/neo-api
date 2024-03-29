<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorCapability.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * NeoModels\ActorCapability
 *
 * @mixin Builder
 * @mixin Model
 */
class ActorCapability extends Pivot {
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
    protected $table = 'actors_capabilities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'actor_id',
        'capability_id',
    ];

    /**
     * The attributes that should be hidden
     *
     * @var list<string>
     */
    protected $hidden = [
        "default",
        "standalone",
        "created_at",
        "updated_at",
    ];

    public static function boot(): void {
        parent::boot();
    }
}
