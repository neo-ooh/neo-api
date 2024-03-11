<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportTemplate.php
 */

namespace Neo\Modules\Demographics\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Models\Traits\HasCreatedByUpdatedBy;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Demographics\Models\Enums\GeographicReportType;
use Neo\Modules\Demographics\Models\StructuredColumns\GeographicReportTemplateConfiguration;
use Spatie\LaravelData\DataCollection;

/**
 * @property int                                                   $id
 * @property string                                                $name
 * @property string                                                $description
 * @property GeographicReportType                                  $type
 * @property DataCollection<GeographicReportTemplateConfiguration> $configuration
 *
 * @property Carbon                                                $created_at
 * @property int|null                                              $created_by
 * @property Carbon                                                $updated_at
 * @property int|null                                              $updated_by
 * @property Carbon|null                                           $deleted_at
 * @property int|null                                              $deleted_by
 *
 * @property-read Collection<GeographicReport>                     $reports
 */
class GeographicReportTemplate extends Model {
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
    protected $table = "geographic_reports_templates";

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public $casts = [
        "type"          => GeographicReportType::class,
        "configuration" => DataCollection::class . ":" . GeographicReportTemplateConfiguration::class,
    ];

    protected function getPublicRelations() {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function reports(): HasMany {
        return $this->hasMany(GeographicReport::class, "template_id", "id");
    }
}
