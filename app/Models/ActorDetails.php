<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorDetails.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Neo\Models\Branding
 *
 * @property int      id
 * @property int      parent_id
 * @property int      parent_is_group
 * @property string   is_property
 * @property bool     direct_children_count
 * @property string   path_names
 * @property string   path_ids
 *
 * @mixin Builder
 */
class ActorDetails extends Model {
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
    protected $table = 'actors_details';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'parent_is_group' => 'boolean',
        'is_property' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function actor (): BelongsTo {
        return $this->belongsTo(Actor::class, 'id', 'id');
    }
}
