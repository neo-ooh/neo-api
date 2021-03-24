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
use Illuminate\Notifications\Notifiable;

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
class AccessToken extends Model  implements AuthenticatableContract, AuthorizableContract {
    use Authenticatable, Authorizable;

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

    /**
     * The accessors to append to the model"s array form.
     *
     * @var array
     */
    protected $with = [
        "capabilities",
    ];

    /**
     * The accessors to append to the model"s array form.
     *
     * @var array
     */
    protected $appends = [];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function capabilities() {
        return $this->belongsToMany(Capability::class, "access_tokens_capabilities", "access_token_id", "capability_id");
    }
}