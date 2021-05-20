<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Frame.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Models\Frame Model
 *
 * @property int                    $id
 * @property int                    $layout_id
 * @property string                 $name
 * @property int                    $width
 * @property int                    $height
 * @property int                    $criteria_id
 *
 * @property FrameSettingsBroadSign $settings_broadsign
 * @property FrameSettingsPiSignage $settings_pisignage
 * @property FormatLayout           $layout
 * @property Collection<Creative>   $creatives
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
     * @var array
     */
    protected $fillable = [
        'format_id',
        'name',
        'width',
        'height',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'width'  => 'integer',
        'height' => 'integer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $appends = [
        'settings_broadsign',
        'settings_pisignage',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function settings_broadsign() {
        return $this->hasOne(FrameSettingsBroadSign::class, "frame_id", "id");
    }

    public function settings_pisignage() {
        return $this->hasOne(FrameSettingsBroadSign::class, "frame_id", "id");
    }

    public function layout(): BelongsTo {
        return $this->belongsTo(FormatLayout::class, 'layout_id', 'id');
    }

    public function creatives(): HasMany {
        return $this->hasMany(Creative::class, 'frame_id', 'id');
    }
}
