<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Layout.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Modules\Broadcast\Models\Layout
 *
 * @property int                      $id
 * @property string                   $name
 * @property boolean                  $is_fullscreen
 * @property Date                     $created_at
 * @property Date                     $updated_at
 * @property Date                     $deleted_at
 *
 * @property Collection<Format>       $formats
 * @property Collection<Frame>        $frames
 * @property Collection<BroadcastTag> $broadcast_tags
 *
 * @mixin Builder
 */
class Layout extends Model {
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
    protected $table = 'layouts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "name",
        "is_fullscreen",
    ];

    /**
     * The attributes that should be casted
     *
     * @var array<string, string>
     */
    protected $casts = [
        "is_fullscreen" => "boolean",
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany<Format>
     */
    public function formats(): BelongsToMany {
        return $this->belongsToMany(Format::class, "format_layouts", "layout_id", 'format_id');
    }

    /**
     * @return HasMany<Frame>
     */
    public function frames(): HasMany {
        return $this->hasMany(Frame::class, 'layout_id', 'id');
    }

    /**
     * @return BelongsToMany<BroadcastTag>
     */
    public function broadcast_tags(): BelongsToMany {
        return $this->belongsToMany(BroadcastTag::class, 'layout_broadcast_tags', 'layout_id', 'broadcast_tag_id');
    }
}
