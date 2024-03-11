<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatasetDatapoint.php
 */

namespace Neo\Modules\Demographics\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int                           $id
 * @property int                           $dataset_version_id
 * @property string                        $code
 * @property string                        $label_en
 * @property string                        $label_fr
 * @property int|null                      $reference_datapoint_id
 *
 * @property-read DatasetVersion           $dataset_version
 * @property-read Collection<DatasetValue> $values
 */
class DatasetDatapoint extends Model {
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
    protected $table = "datasets_datapoints";

    public $timestamps = false;

    protected $fillable = [
        "dataset_version_id",
        "code",
        "label_en",
        "label_fr",
        "reference_datapoint_id",
    ];

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

    public function dataset_version(): BelongsTo {
        return $this->belongsTo(Dataset::class, "dataset_id", "id");
    }

    public function values(): HasMany {
        return $this->hasMany(DatasetValue::class, "datapoint_id", "id");
    }

}
