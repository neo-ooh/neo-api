<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Screenshot.php
 */

/** @noinspection PhpMissingFieldTypeInspection */

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Neo\Http\Controllers\BurstsController;

/**
 * Neo\Models\Screenshot
 *
 * @property int   id
 * @property int   burst_id
 * @property Date  created_at
 *
 * @property Burst burst
 *
 * @property string file_path
 *
 * @mixin Builder
 */
class Screenshot extends Model {
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
    protected $table = 'screenshots';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'burst_id',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function burst (): BelongsTo {
        return $this->belongsTo(Burst::class, 'burst_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Mechanisms
    |--------------------------------------------------------------------------
    */

    public function getFilePathAttribute() {
        return "/bursts/{$this->burst_id}/{$this->id}.jpg";
    }

    /**
     * @param resource $screenshot
     */
    public function store($screenshot) {
        // And store the request
        Storage::writeStream($this->file_path, $screenshot, ["visibility" => "public"]);
    }
}
