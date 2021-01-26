<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - TwoFactorToken.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Neo\Mails\TwoFactorTokenEmail;

/**
 * Class TwoFactorToken
 *
 * @package App
 *
 * @property int     actor_id
 * @property int     token
 * @property boolean validated
 * @property Date    created_at
 * @property Date    validated_at
 *
 *
 * @mixin Builder
 */
class TwoFactorToken extends Model {
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
    protected $table = 'two-factor-tokens';

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'actor_id',
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
        'validated_at',
        'updated_at',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'validated' => 'boolean',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    public static function boot () {
        parent::boot();

        static::creating(function (TwoFactorToken $model) {
            $model->token = str_pad(random_int(1000, 999999), 6, '0', STR_PAD_LEFT);
            $model->created_at = $model->freshTimestamp();
            Mail::to($model->actor)->send(new TwoFactorTokenEmail($model));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function actor (): BelongsTo {
        return $this->belongsTo(Actor::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * @param string $token
     *
     * @return bool
     */
    public function validate (string $token): bool {
        if ($this->token !== $token) {
            // Bad token
            return false;
        }

        // Good token
        $this->validated = true;
        $this->validated_at = $this->freshTimestamp();
        $this->save();

        return true;
    }
}
