<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RecoveryToken.php
 */

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
 * @property string $token
 * @property Date   $created_at
 *
 * @property string $email
 * @property Actor  $actor
 *
 * @mixin Builder<RecoveryToken>
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
     * @var array<string>
     */
    protected $fillable = [
        'email',
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
    public $casts = [
        "created_at" => "datetime",
    ];


    public static function boot(): void {
        parent::boot();

        static::creating(function (RecoveryToken $model) {
            $model->token      = Str::random(32);
            $model->created_at = $model->freshTimestamp();

            /** @var Actor $actor */
            $actor = $model->actor()->first();

            Mail::to($model->actor)->send(new RecoverPasswordEmail($actor, $model));
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
