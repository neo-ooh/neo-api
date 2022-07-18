<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Format.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Models\Formats
 *
 * @property int                      $id
 * @property string                   $name
 * @property boolean                  $is_fullscreen
 * @property boolean                  $is_enabled
 *
 * @property Collection<FormatLayout> $layouts
 * @property Collection<DisplayType>  $display_types
 * @property Collection<Content>      $contents
 * @property Collection<Campaign>     $campaigns
 * @property Collection<Location>     $locations
 *
 * @property int                      $contents_count
 * @property int                      $campaigns_count
 * @property int                      $locations_count
 *
 * @mixin Builder<Format>
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
     * @var array<string>
     */
    protected $fillable = [
        "name",
        "is_enabled",
    ];

    /**
     * The attributes that should be casted to other types
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_fullscreen" => "boolean",
        "is_enabled"    => "boolean",
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ["layouts", "display_types"];

    /**
     * The relationship counts that should always be loaded.
     *
     * @var array
     */
    protected $withCount = ["layouts", "display_types"];

    protected $hidden = ["slug"];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany<DisplayType>
     */
    public function display_types(): BelongsToMany {
        return $this->belongsToMany(DisplayType::class, 'formats_display_types', 'format_id', "display_type_id");
    }

    /**
     * @return HasMany<FormatLayout>
     */
    public function layouts(): HasMany {
        return $this->hasMany(FormatLayout::class, 'format_id', 'id');
    }

    /**
     * @return BelongsToMany<Location>
     */
    public function locations(): BelongsToMany {
        return $this->belongsToMany(Location::class, 'locations_formats', 'format_id', 'location_id');
    }
}