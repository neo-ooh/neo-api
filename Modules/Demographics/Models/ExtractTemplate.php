<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExtractTemplate.php
 */

namespace Neo\Modules\Demographics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int                           $id
 * @property string                        $name
 * @property string                        $description
 * @property int                           $dataset_version_id
 * @property int                           $geographic_report_template_id
 *
 * @property Carbon                        $created_at
 * @property int|null                      $created_by
 * @property Carbon                        $updated_at
 * @property int|null                      $updated_by
 * @property Carbon|null                   $deleted_at
 * @property int|null                      $deleted_by
 *
 * @property-read DatasetVersion           $dataset_version
 * @property-read GeographicReportTemplate $geographic_report_template
 * @property-read Collection<Extract>      $extracts
 */
class ExtractTemplate extends Model {
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
    protected $connection = "neo_demographics";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "extracts_templates";

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
        return $this->belongsTo(DatasetVersion::class, "dataset_version_id", "id");
    }

    public function geographic_report_template(): BelongsTo {
        return $this->belongsTo(GeographicReportTemplate::class, "geographic_report_template_id", "id");
    }

    public function extracts(): HasMany {
        return $this->hasMany(Extract::class, "template_id", "id");
    }
}
