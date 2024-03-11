<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportValue.php
 */

namespace Neo\Modules\Demographics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int                   $report_id
 * @property int                   $area_id
 * @property double                $geography_weight
 * @property array                 $metadata
 *
 * @property-read GeographicReport $report
 * @property-read Area             $area
 */
class GeographicReportValue extends Model {
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
    protected $table = "geographic_reports_values";

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

    public function report(): BelongsTo {
        return $this->belongsTo(GeographicReport::class, "report_id", "id");
    }

    public function area(): BelongsTo {
        return $this->belongsTo(Area::class, "area_id", "id");
    }
}
