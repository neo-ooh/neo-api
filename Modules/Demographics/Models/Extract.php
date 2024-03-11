<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Extract.php
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
use Neo\Modules\Demographics\Models\StructuredColumns\ExtractMetadata;
use function Ramsey\Uuid\v4;

/**
 * @property int                           $id
 * @property string                        $uuid
 * @property int                           $template_id
 * @property int                           $property_id
 * @property int                           $geographic_report_id
 * @property ExtractMetadata               $metadata
 * @property ReportStatus                  $status
 *
 * @property Carbon                        $requested_at
 * @property int|null                      $requested_by
 * @property Carbon                        $processed_at
 * @property Carbon|null                   $deleted_at
 * @property int|null                      $deleted_by
 *
 * @property-read ExtractTemplate          $template
 * @property-read GeographicReport         $geographic_report
 * @property-read Collection<ExtractValue> $values
 */
class Extract extends Model {
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
    protected $table = "extracts";

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
        "metadata" => ExtractMetadata::class,
    ];

    protected $fillable = [
        "uuid",
        "template_id",
        "property_id",
        "geographic_report_id",
        "metadata",
        "status",
        "requested_at",
        "requested_by",
        "processed_at",
        "deleted_by",
    ];

    protected function getPublicRelations() {
        return [
            "template" => Relation::make(
                load: "template"
            ),
            "geographic_report"   => Relation::make(
                load: "geographic_report"
            ),
            "values"   => Relation::make(
                load: "values"
            ),
        ];
    }

    protected static function boot() {
        parent::boot();

        static::creating(static function (Extract $report) {
            $report->uuid = v4();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function template(): BelongsTo {
        return $this->belongsTo(ExtractTemplate::class, "template_id", "id");
    }

    public function geographic_report(): BelongsTo {
        return $this->belongsTo(GeographicReport::class, "geographic_report_id", "id");
    }

    public function values(): HasMany {
        return $this->hasMany(ExtractValue::class, "extract_id", "id");
    }

}
