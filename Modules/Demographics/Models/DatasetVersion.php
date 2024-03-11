<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatasetVersion.php
 */

namespace Neo\Modules\Demographics\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Demographics\Models\Enums\DatasetStructure;

/**
 * @property int                          $id
 * @property int                          $dataset_id
 * @property string                       $name
 * @property string                       $provider
 * @property DatasetStructure             $structure
 * @property int                          $order
 * @property bool                         $is_primary
 * @property bool                         $is_archived
 * @property Carbon                       $created_at
 * @property Carbon                       $updated_at
 *
 * @property-read Dataset                      $dataset
 * @property-read Collection<DatasetDatapoint> $datapoints
 */
class DatasetVersion extends Model {
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
    protected $table = "datasets_versions";

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        "structure"   => DatasetStructure::class,
        "is_archived" => "boolean",
        "is_primary" => "boolean",
    ];

    protected function getPublicRelations() {
        return [
            "dataset" => Relation::make(
                load: ["dataset"]
            ),
            "datapoints" => Relation::make(
                load: ["datapoints"]
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function dataset(): BelongsTo {
        return $this->belongsTo(Dataset::class, "dataset_id", "id");
    }

    public function datapoints(): HasMany {
        return $this->hasMany(DatasetDatapoint::class, "dataset_version_id", "id");
    }

}
