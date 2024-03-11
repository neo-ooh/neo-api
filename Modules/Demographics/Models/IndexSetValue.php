<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IndexSetValue.php
 */

namespace Neo\Modules\Demographics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int           $set_id
 * @property int           $datapoint_id
 * @property double        $primary_value
 * @property double        $reference_value
 * @property-read int      $index
 *
 * @property-read IndexSet $set
 */
class IndexSetValue extends Model {
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
    protected $connection = "neo_ooh";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "index_sets_values";

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

    protected $fillable = [
        "set_id",
        "datapoint_id",
        "primary_value",
        "reference_value",
    ];

    protected function getPublicRelations() {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function set(): BelongsTo {
        return $this->belongsTo(IndexSet::class, "set_id", "id");
    }
}
