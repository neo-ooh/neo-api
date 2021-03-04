<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Location.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Neo\Rules\AccessibleLocation;

/**
 * Neo\Models\ActorsLocations
 *
 * @property int        id
 * @property int        broadsign_display_unit
 * @property int        display_type_id
 * @property int        network
 * @property string     name
 * @property string     internal_name
 *
 * @property ?Container container
 *
 * @mixin Builder
 */
class Location extends SecuredModel {
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
    protected $table = 'locations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "broadsign_display_unit",
        "format_id",
        "name",
        "internal_name",
        "container_id",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "broadsign_display_unit",
        "internal_name",
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [ "format" ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleLocation::class;


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Network */

    public function players (): HasMany {
        return $this->hasMany(Player::class);
    }

    /**
     * @return BelongsTo
     * @deprecated WILL NOT WORK!!!
     */
    public function format (): BelongsTo {
        return $this->belongsTo(Format::class);
    }

    public function display_type (): BelongsTo {
        return $this->belongsTo(DisplayType::class, "display_type_id");
    }

    public function container (): BelongsTo {
        return $this->belongsTo(Container::class);
    }

    /* Reports */

    public function bursts (): HasManyThrough {
        return $this->hasManyThrough(Burst::class, Player::class);
    }

    public function reports (): HasManyThrough {
        return $this->hasManyThrough(Report::class, Player::class);
    }


    /*
    |--------------------------------------------------------------------------
    | ***
    |--------------------------------------------------------------------------
    */

    public function loadHierarchy (): self {
        if ($this->container !== null) {
            $this->container->append('parents_list');
        }

        return $this;
    }
}
