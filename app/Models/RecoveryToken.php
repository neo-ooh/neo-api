<?php
//------------------------------------------------------------------------------
// Copyright 2020 (c) Neo-OOH - All Rights Reserved
// Unauthorized copying of this file, via any medium is strictly prohibited
// Proprietary and confidential
// Written by Valentin Dufois <Valentin Dufois>
//
// neo-auth - RecoveryToken.php
//------------------------------------------------------------------------------

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Neo\Mails\RecoverPasswordEmail;

/**
 * Class RecoveryToken
 *
 * @package App
 * @mixin Builder
 * @property string token
 * @property Date   created_at
 *
 * @property string email
 * @property Actor actor
 */
class RecoveryToken extends Model {
    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'email';

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
        'email',
        'token',
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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];


    public static function boot(): void {
        parent::boot();

        static::creating(function (RecoveryToken $model) {
            $model->token      = Str::random(32);
            $model->created_at = $model->freshTimestamp();

            Mail::to($model->actor)->send(new RecoverPasswordEmail($model));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, 'email', 'email');
    }
}
