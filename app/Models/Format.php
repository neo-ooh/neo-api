<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Format.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Models\Formats
 *
 * @property int                  id
 * @property int                  broadsign_display_type
 * @property string               slug
 * @property string               name
 * @property boolean              is_enabled
 *
 * @property Collection<Frame>    frames
 * @property Collection<Content>  contents
 * @property Collection<Campaign> campaigns
 * @property Collection<Location> locations
 *
 * @property int                  frames_count
 * @property int                  contents_count
 * @property int                  campaigns_count
 * @property int                  locations_count
 *
 * @mixin Builder
 */
class Format extends Model {
    use SoftDeletes;

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
    protected $table = 'formats';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "broadsign_display_type",
        "slug",
        "name",
    ];

    /**
     * The attributes that should be casted to other types
     *
     * @var array
     */
    protected $casts = [
        "is_enabled" => "boolean",
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [ "frames" ];

    /**
     * The relationship counts that should always be loaded.
     *
     * @var array
     */
    protected $withCount = [ "frames" ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Network */

    public function frames (): HasMany {
        return $this->hasMany(Frame::class, 'format_id', 'id');
    }

    public function locations (): BelongsToMany {
        return $this->belongsToMany(Location::class, 'locations_formats', 'format_id', 'location_id');
    }
}
