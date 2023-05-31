<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TwoFactorToken.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Neo\Jobs\Actors\SendTwoFactorTokenJob;

/**
 * Class TwoFactorToken
 *
 * @package App
 *
 * @property int     $actor_id
 * @property string  $token
 * @property boolean $validated
 * @property Date    $created_at
 * @property Date    $validated_at
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

    public const UPDATED_AT = null;

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
     * @var array<string>
     */
    protected $fillable = [
        'actor_id',
        'token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var list<string>
     */
    protected $hidden = [
        'token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'token'        => 'integer',
        'validated'    => 'boolean',
        'validated_at' => 'date',
    ];

    public static function boot() {
        parent::boot();

        static::creating(static function (TwoFactorToken $model) {
            Log::error("New 2FA Token");
            $model->token      = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $model->created_at = $model->freshTimestamp();
        });

        static::created(static function (TwoFactorToken $model) {
            SendTwoFactorTokenJob::dispatch($model->actor_id);
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
    public function actor(): BelongsTo {
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
    public function validate(string $token): bool {
        if (strcmp($this->token, $token) !== 0) {
            // Bad token
            return false;
        }

        // Good token
        $this->validated    = true;
        $this->validated_at = $this->freshTimestamp();
        $this->save();

        return true;
    }
}
