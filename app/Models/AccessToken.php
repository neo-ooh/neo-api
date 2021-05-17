<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessToken.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Neo\Models\Traits\HasCapabilities;
use Ramsey\Uuid\Uuid;

/**
 * Class Actor
 *
 * @package Neo\Base
 *
 * @property int    id
 * @property string name
 * @property string token
 * @property Date   created_at
 * @property Date   updated_at
 *
 * @mixin Builder
 */
class AccessToken extends Model implements AuthenticatableContract, AuthorizableContract {
    use Authenticatable, Authorizable;
    use HasCapabilities;

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
    protected $table = "access_tokens";

    protected $dates = [
        "last_used_at",
    ];

    /**
     * The accessors to append to the model"s array form.
     *
     * @var array
     */
    protected $with = [
        "capabilities",
    ];

    public static function boot() {
        parent::boot();

        static::creating(function (AccessToken $model) {
            $model->token = Uuid::uuid4()->toString();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function capabilities() {
        return $this->belongsToMany(Capability::class, "access_tokens_capabilities", "access_token_id", "capability_id");
    }
}
