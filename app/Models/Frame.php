<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Frame.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Models\Frame Model
 *
 * @property int                  id
 * @property int                  layout_id
 * @property string               name
 * @property int                  width
 * @property int                  height
 * @property int                  criteria_id
 *
 * @property FormatLayout         layout
 * @property Collection<Creative> creatives
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
        'criteria_id'
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


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function layout(): BelongsTo {
        return $this->belongsTo(FormatLayout::class, 'layout_id', 'id');
    }

    public function creatives(): HasMany {
        return $this->hasMany(Creative::class, 'frame_id', 'id');
    }

    public function criteria(): HasOne {
        return $this->hasOne(BroadSignCriteria::class, "criteria_id");
    }
}
