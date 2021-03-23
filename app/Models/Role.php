<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Role.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Neo\Models\Role
 *
 * @property int                    id
 * @property string                 name
 * @property string                 desc
 * @property Date                   created_at
 * @property Date                   updated_at
 *
 * @property Collection<Capability> capabilities
 * @property Collection<Actor>      actors
 *
 * @mixin Builder
 */
class Role extends Model {
    use HasFactory;

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
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'desc',
    ];

    protected static function newFactory (): Factories\RoleFactory {
        return Factories\RoleFactory::new();
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * All capabilities in the role
     */
    public function capabilities (): BelongsToMany {
        return $this->belongsToMany(
            Capability::class,
            'roles_capabilities',
            'role_id',
            'capability_id')
                    ->withPivot('value')->as('details')
                    ->withTimestamps();
    }

    /**
     * All users with this role
     */
    public function actors (): BelongsToMany {
        return $this->belongsToMany(
            Actor::class,
            'actors_roles',
            'role_id',
            'actor_id')
                    ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | Custom mechanisms
    |--------------------------------------------------------------------------
    */
}
