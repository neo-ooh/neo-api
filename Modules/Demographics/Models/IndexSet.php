<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IndexSet.php
 */

namespace Neo\Modules\Demographics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\StructuredColumns\IndexSetMetadata;

/**
 * @property int                            $id
 * @property int                            $template_id
 * @property int                            $property_id
 * @property int                            $primary_extract_id
 * @property int                            $reference_extract_id
 * @property IndexSetMetadata                          $metadata
 * @property ReportStatus                   $status
 *
 * @property Carbon                         $requested_at
 * @property int|null                       $requested_by
 * @property Carbon                         $processed_at
 * @property Carbon|null                    $deleted_at
 * @property int|null                       $deleted_by
 *
 * @property-read IndexSetTemplate          $template
 * @property-read Collection<IndexSetValue> $values
 */
class IndexSet extends Model {
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
    protected $table = "index_sets";

    public const CREATED_AT = "requested_at";

    public function getCreatedByColumn(): string|null {
        return "requested_by";
    }

    public const UPDATED_AT = null;

    public function getUpdatedByColumn(): string|null {
        return null;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        "status"   => ReportStatus::class,
        "metadata" => IndexSetMetadata::class,
    ];

    protected $fillable = [
        "template_id",
        "property_id",
        "primary_extract_id",
        "reference_extract_id",
        "metadata",
        "status",
        "requested_by",
        "processed_at",
    ];

    protected function getPublicRelations() {
        return [
            "template" => Relation::make(
                load: "template"
            ),
            "values" => Relation::make(
                load: "values"
            ),

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function template(): BelongsTo {
        return $this->belongsTo(IndexSetTemplate::class, "template_id", "id");
    }

    public function values(): HasMany {
        return $this->hasMany(IndexSetValue::class, "set_id", "id");
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */
}
