<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorClosure.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NeoModels\Branding
 *
 * @property int   $ancestor_id
 * @property int   $descendant_id
 * @property int   $depth
 *
 * @property Actor $ancestor
 * @property Actor $descendant
 *
 * @mixin Builder
 */
class ActorClosure extends Model {
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
    protected $table = 'actors_closures';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "ancestor_id",
        "descendant_id",
        "depth",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function ancestor(): BelongsTo {
        return $this->belongsTo(Actor::class, 'ancestor_id', 'id');
    }

    public function descendant(): BelongsTo {
        return $this->belongsTo(Actor::class, 'descendant_id', 'id');
    }
}
