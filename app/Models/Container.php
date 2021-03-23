<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Container.php
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Locally stored representation of a Container inside BroadSign.
 * Containers are cached on our side to prevent too much fetching to the API
 * Everytime we need to show the hierarchy of containers
 *
 * @property int             id
 * @property int             parent_id
 * @property string          name
 *
 * @property-read ?Container parent
 * @property-read Collection parents_list
 *
 * @mixin Builder
 */
class Container extends Model {
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
    protected $table = 'containers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "id",
        "parent_id",
        "name",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * @return Collection
     */
    public function getParentsListAttribute(): Collection {
        if (is_null($this->parent_id)) {
            return new Collection();
        }

        return $this->hydrate(DB::select(/** @lang MySQL */ "
            WITH RECURSIVE cte (id, name, parent_id) AS (
                  SELECT id,
                         name,
                         parent_id
                    FROM containers
                   WHERE id = ?
               UNION ALL
                  SELECT c.id,
                         c.name,
                         c.parent_id
                    FROM containers c
              INNER JOIN cte
                      ON c.id = cte.parent_id
            )
            SELECT * FROM cte",
            [$this->parent_id]
        ));
    }
}
