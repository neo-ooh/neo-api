<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - SignupToken.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Class SignupToken
 *
 * @package Neo
 * @mixin Builder
 * @property int        $actor_id
 * @property string     $token
 * @property Carbon     $created_at
 * @property-read Actor $actor
 * @method static Builder|SignupToken newModelQuery()
 * @method static Builder|SignupToken newQuery()
 * @method static Builder|SignupToken query()
 * @method static Builder|SignupToken whereCreatedAt($value)
 * @method static Builder|SignupToken whereToken($value)
 * @method static Builder|SignupToken whereUserId($value)
 */
class SignupToken extends Model {
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
    protected $table = 'signup_tokens';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'actor_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'actor_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * The attributes that should be mutated to date.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    public $timestamps = false;

    public static function boot (): void {
        parent::boot();

        static::creating(function (SignupToken $model) {
            $model->token = Str::random(32);
            $model->created_at = $model->freshTimestamp();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function actor (): BelongsTo {
        return $this->belongsTo(Actor::class);
    }
}
