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
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Neo\Models\Branding
 *
 * @property int                  id
 * @property int                  format_id
 * @property string               name
 * @property int                  width
 * @property int                  height
 * @property string               type
 *
 * @property Format               format
 * @property Collection<Creative> creatives
 *
 * @mixin Builder
 */
class Frame extends Model {
    use SoftDeletes;

    // Define the supported frame types
    public const TYPE_MAIN = 'MAIN';
    public const TYPE_RIGHT = 'RIGHT';

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
        'type'
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

    /* Network */

    public function format (): BelongsTo {
        return $this->belongsTo(Format::class, 'format_id', 'id');
    }

    /* Direct */

    public function creatives (): HasMany {
        return $this->hasMany(Creative::class, 'frame_id', 'id');
    }
}
