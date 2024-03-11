<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExtractValue.php
 */

namespace Neo\Modules\Demographics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int    $extract_id
 * @property int    $datapoint_id
 * @property double $value
 *
 * @property-read Extract $extract
 * @property-read DatasetDatapoint $datapoint
 */
class ExtractValue extends Model {
    use HasPublicRelations;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    /**
     * The database of the model's table.
     *
     * @var string
     */
    protected $connection = "neo_demographics";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "extracts_values";

    /**
     * The model's primary key
     *
     * @var null
     */
    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [];

    protected function getPublicRelations() {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function extract(): BelongsTo {
        return $this->belongsTo(Extract::class, "extract_id", "id");
    }

    public function datapoint(): BelongsTo {
        return $this->belongsTo(DatasetDatapoint::class, "datapoint_id", "id");
    }


}
