<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Report.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Neo\Models\ActorsLocations
 *
 * @property int    id
 * @property int    player_id
 * @property string name
 * @property int    created_by
 *
 * @property Player player
 * @property Actor   creator
 *
 * @mixin Builder
 */
class Report extends Model {
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
    protected $table = 'reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "player_id",
        "name",
        "created_by",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function player (): BelongsTo {
        return $this->belongsTo(Player::class);
    }

    public function creator (): BelongsTo {
        return $this->belongsTo(Actor::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | ***
    |--------------------------------------------------------------------------
    */
}
