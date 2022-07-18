<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Location.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\Actor;
use Neo\Models\Container;
use Neo\Models\ContractBurst;
use Neo\Models\Product;
use Neo\Models\SecuredModel;
use Neo\Modules\Broadcast\Rules\AccessibleLocation;

/**
 * Neo\Models\ActorsLocations
 *
 * @property int                 $id
 * @property int                 $network_id
 * @property string              $external_id
 * @property int                 $display_type_id
 * @property string              $name
 * @property string              $internal_name
 * @property int                 $container_id
 * @property string              $province [QC, ON, ...]
 * @property string              $city
 * @property boolean             $scheduled_sleep
 * @property Date                $sleep_end
 * @property Date                $sleep_start
 * @property Date                $created_at
 * @property Date                $updated_at
 *
 * @property ?Container          $container
 * @property Network             $network
 * @property Collection<Product> $locations
 * @property Collection<Player>  $players
 * @property DisplayType         $display_type
 * @property Collection<Product> $products
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
        "external_id",
        "network_id",
        "format_id",
        "name",
        "internal_name",
        "container_id",
        "province",
        "city",
    ];

    protected $casts = [
        "scheduled_sleep" => "boolean"
    ];

    protected $dates = [
        "sleep_end",
        "sleep_start",
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        "display_type",
        "network:id,name"
    ];

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

    public function network(): BelongsTo {
        return $this->belongsTo(Network::class, "network_id");
    }

    public function players(): HasMany {
        return $this->hasMany(Player::class, "location_id");
    }

    public function display_type(): BelongsTo {
        return $this->belongsTo(DisplayType::class, "display_type_id");
    }

    public function container(): BelongsTo {
        return $this->belongsTo(Container::class, "container_id");
    }

    public function actors(): BelongsToMany {
        return $this->belongsToMany(Actor::class, "actors_locations", "location_id", "actor_id");
    }

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, "products_locations", "location_id", "product_id");
    }

    /* Reports */

    public function bursts(): HasMany {
        return $this->hasMany(ContractBurst::class, "location_id");
    }


    /*
    |--------------------------------------------------------------------------
    | ***
    |--------------------------------------------------------------------------
    */

    public function loadHierarchy(): self {
        $this->container?->append('parents_list');

        return $this;
    }
}