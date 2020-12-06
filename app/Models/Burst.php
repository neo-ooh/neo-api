<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Burst.php
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Neo\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon as Date;

/**
 * Class Burst
 *
 * @package Neo\Models
 *
 * @property int    id
 * @property int    player_id
 * @property int    requested_by
 * @property Date   started_at
 * @property string status
 * @property bool   is_manual
 * @property int    scale_factor
 * @property int    duration_ms
 * @property int    frequency_ms
 * @property Date   created_at
 * @property Date   updated_at
 *
 * @property Player player
 *
 * @mixin Builder
 */
class Burst extends Model {
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
    protected $table = 'bursts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'player_id',
        'requested_by',
        'started_at',
        'status',
        'is_manual',
        'scale_factor',
        'duration_ms',
        'frequency_ms',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        "started_at",
    ];
    protected $with = [
        "screenshots",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function player (): BelongsTo {
        return $this->belongsTo(Player::class, "player_id");
    }

    /**
     * @return BelongsTo
     */
    public function location (): BelongsTo {
        return $this->player->location();
    }

    /**
     * @return BelongsTo
     */
    public function screenshots (): HasMany {
        return $this->hasMany(Screenshot::class);
    }

    /**
     * @return BelongsTo
     */
    public function owner (): BelongsTo {
        return $this->belongsTo(Actor::class, "requested_by");
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Mechanism
    |--------------------------------------------------------------------------
    */

    /**
     *
     */
    public function execute (): void {
        if ($this->status !== 'planned') {
            return; // Do not execute a burst that has already been done
        }
    }
}
