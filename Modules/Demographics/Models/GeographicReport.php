<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReport.php
 */

namespace Neo\Modules\Demographics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Demographics\Models\Enums\GeographicReportType;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\StructuredColumns\GeographicReportMetadata;
use Neo\Modules\Demographics\Models\StructuredColumns\GeographicReportTemplateConfiguration;
use function Ramsey\Uuid\v4;

/**
 * @property int                                    $id
 * @property string                                 $uuid
 * @property int                                    $template_id
 * @property int                                    $property_id
 * @property GeographicReportMetadata               $metadata
 * @property ReportStatus                           $status
 *
 * @property Carbon                                 $requested_at
 * @property int|null                               $requested_by
 * @property Carbon                                 $processed_at
 * @property Carbon|null                            $deleted_at
 * @property int|null                               $deleted_by
 *
 * @property-read GeographicReportTemplate          $template
 * @property-read Collection<GeographicReportValue> $values
 *
 * @property-read string                            $source_file_path
 */
class GeographicReport extends Model {
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
    protected $table = "geographic_reports";

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
        "status" => ReportStatus::class,
        "metadata" => GeographicReportMetadata::class,
    ];

    protected function getPublicRelations() {
        return [
            "template" => Relation::make(
                load: "template"
            ),
            "values" => Relation::make(
                load: "values"
            ),
            "areas" => Relation::make(
                load: "values.area"
            ),
        ];
    }

    protected static function boot() {
        parent::boot();

        static::creating(static function (GeographicReport $report) {
            $report->uuid = v4();
        });

        static::deleting(static function (GeographicReport $report) {
            $report->deleteSourceFile();
        });
    }

    public static function fromTemplate(GeographicReportTemplate $template, int $configurationBlockIndex) {
        $report = new static();
        $report->template_id = $template->getKey();
        $report->metadata = GeographicReportMetadata::from([]);
        $report->status = ReportStatus::Pending;

        /** @var GeographicReportTemplateConfiguration $configBlock */
        $configBlock = $template->configuration[$configurationBlockIndex];

        if($template->type === GeographicReportType::Area) {
            $report->metadata->area_type = $configBlock->area_type;
            $report->metadata->distance = $configBlock->distance;
            $report->metadata->distance_unit = $configBlock->distance_unit;
            $report->metadata->routing = $configBlock->routing;
        }

        return $report;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function template(): BelongsTo {
        return $this->belongsTo(GeographicReportTemplate::class, "template_id", "id");
    }

    public function values(): HasMany {
        return $this->hasMany(GeographicReportValue::class, "report_id", "id");
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function getSourceFilePathAttribute() {
        return "demographics/geographic_reports_source_files/" . $this->uuid . "." . $this->metadata->source_file_type;
    }

    public function storeSourceFile($file) {
        Storage::disk("public")
               ->putFileAs("demographics/geographic_reports_source_files/", $file, $this->uuid . "." . $this->metadata->source_file_type);
    }

    public function deleteSourceFile() {
        Storage::disk("public")
               ->delete($this->source_file_path);
    }
}
