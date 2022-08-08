<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayType.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Models\Formats
 *
 * @property int                           $id
 * @property string                        $slug
 * @property int                           $network_id
 * @property string                        $name
 * @property boolean                       $is_enabled
 *
 * @property Collection<Layout>            $layouts
 * @property Collection<DisplayType>       $display_types
 * @property Collection<BroadcastTag>      $broadcast_tags
 * @property Collection<LoopConfiguration> $loop_configurations
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
        "network_id",
        "name",
        "is_enabled",
    ];

    /**
     * The attributes that should be casted to other types
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_enabled" => "boolean",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasOne<Network>
     */
    public function network(): HasOne {
        return $this->hasOne(Network::class, "network_id");
    }

    /**
     * @return BelongsToMany<DisplayType>
     */
    public function display_types(): BelongsToMany {
        return $this->belongsToMany(DisplayType::class, 'format_display_types', 'format_id', "display_type_id");
    }

    /**
     * @return BelongsToMany<Layout>
     */
    public function layouts(): BelongsToMany {
        return $this->belongsToMany(Layout::class, 'format_layouts', 'format_id', 'layout_id');
    }

    /**
     * @return BelongsToMany<BroadcastTag>
     */
    public function broadcast_tags(): BelongsToMany {
        return $this->belongsToMany(BroadcastTag::class, 'format_broadcast_tags', 'format_id', 'broadcast_tag_id');
    }

    /**
     * @return BelongsToMany<LoopConfiguration>
     */
    public function loop_configurations(): BelongsToMany {
        return $this->belongsToMany(LoopConfiguration::class, 'format_loop_configurations', 'format_id', 'loop_configuration_id');
    }
}
