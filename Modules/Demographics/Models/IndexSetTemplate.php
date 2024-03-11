<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IndexSetTemplate.php
 */

namespace Neo\Modules\Demographics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int                       $id
 * @property string                    $name
 * @property string                    $description
 * @property int                       $dataset_version_id
 * @property int                       $primary_extract_template_id
 * @property int                       $reference_extract_template_id
 *
 *
 * @property Carbon                    $created_at
 * @property int|null                  $created_by
 * @property Carbon                    $updated_at
 * @property int|null                  $updated_by
 * @property Carbon|null               $deleted_at
 * @property int|null                  $deleted_by
 *
 * @property-read Collection<IndexSet> $sets
 */
class IndexSetTemplate extends Model {
    use HasPublicRelations;
    use SoftDeletes;
    use HasCreatedByUpdatedBy;

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
    protected $table = "index_sets_templates";

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

    public function sets(): HasMany {
        return $this->hasMany(IndexSet::class, "template_id", "id");
    }
}
