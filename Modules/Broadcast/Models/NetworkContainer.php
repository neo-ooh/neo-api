<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworkContainer.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Locally stored representation of a NetworkContainer inside a broadcaster.
 * Containers are cached on our side to prevent too much fetching to the API
 * Everytime we need to show the hierarchy of containers
 *
 * @property int                     $id
 * @property int|null                $parent_id
 * @property int                     $network_id
 * @property string                  $name
 * @property string|null             $external_id
 *
 * @property Carbon|null             $created_at
 * @property Carbon|null             $updated_at
 *
 * @property-read static|null        $parent
 * @property-read Collection<static> $parents_list
 *
 * @mixin Builder
 */
class NetworkContainer extends Model {
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
    protected $table = 'network_containers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "network_id",
        "parent_id",
        "name",
        "external_id",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<static, static>
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(static::class, "parent_id", "id");
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return Collection<static>
     */
    public function getParentsListAttribute(): Collection {
        /** @var Collection<static> $parents */
        $parents = $this->hydrate(DB::select(/** @lang MySQL */ "
            WITH RECURSIVE `cte` (`id`, `name`, `parent_id`) AS (
                  SELECT `id`,
                         `name`,
                         `parent_id`
                    FROM `network_containers`
                   WHERE `id` = ?
               UNION ALL
                  SELECT `c`.`id`,
                         `c`.`name`,
                         `c`.`parent_id`
                    FROM `network_containers` `c`
              INNER JOIN `cte`
                      ON `c`.`id` = `cte`.`parent_id`
            )
            SELECT * FROM `cte`",
                                                                [$this->parent_id]
        ));

        return $parents;
    }
}
