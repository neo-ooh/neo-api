<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Frame.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Modules\Broadcast\Models\Creative;

/**
 * @property int                      $id
 * @property int                      $layout_id
 * @property string                   $name
 * @property int                      $width
 * @property int                      $height
 *
 * @property Collection<BroadcastTag> $broadcast_tags
 * @property Layout                   $layout
 * @property Collection<Creative>     $creatives
 *
 * @mixin Builder
 */
class Frame extends Model {
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
    protected $table = 'frames';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'layout_id',
        'name',
        'width',
        'height',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */


    /**
     * @return BelongsToMany<BroadcastTag>
     */
    public function broadcast_tags(): BelongsToMany {
        return $this->belongsToMany(BroadcastTag::class, 'frame_broadcast_tags', 'frame_id', 'broadcast_tag_id');
    }

    /**
     * @return BelongsTo<Layout, Frame>
     */
    public function layout(): BelongsTo {
        return $this->belongsTo(Layout::class, 'layout_id', 'id');
    }

    /**
     * @return HasMany<Creative>
     */
    public function creatives(): HasMany {
        return $this->hasMany(Creative::class, 'frame_id', 'id');
    }
}
