<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatLayout.php
 */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Models\FormatLayout
 *
 * @property int                  id
 * @property int                  format_id
 * @property string               name
 * @property boolean              is_fullscreen
 * @property int                  trigger_id
 * @property int                  separation_id
 * @property Date                 created_at
 * @property Date                 updated_at
 * @property Date                 deleted_at
 *
 * @property Format               format
 * @property Collection<Frame>    frames
 * @property ?BroadSignTrigger    trigger
 * @property ?BroadSignSeparation separation
 *
 * @mixin Builder
 */
class FormatLayout extends Model {
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
    protected $table = 'formats_layouts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "format_id",
        "name",
        "is_fullscreen",
        "trigger_id",
        "separation_id",
    ];

    /**
     * The attributes that shoud be casted
     *
     * @var array
     */
    protected $casts = [
        "is_fullscreen" => "boolean",
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ["frames"];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function format(): BelongsTo {
        return $this->belongsTo(Format::class, 'format_id', 'id');
    }

    public function frames(): HasMany {
        return $this->hasMany(Frame::class, 'layout_id', 'id');
    }

    public function trigger(): BelongsTo {
        return $this->belongsTo(BroadSignTrigger::class, 'trigger_id', 'id');
    }

    public function separation(): BelongsTo {
        return $this->belongsTo(BroadSignSeparation::class, 'separation_id', 'id');
    }
}
