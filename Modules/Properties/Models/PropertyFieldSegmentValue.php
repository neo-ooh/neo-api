<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyFieldSegmentValue.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * @property int          $property_id
 * @property int          $fields_segment_id
 * @property double       $value
 * @property double|null  $reference_value
 * @property integer|null $index
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 */
class PropertyFieldSegmentValue extends Model {
    use HasCompositePrimaryKey;

    protected $table = "properties_fields_segments_values";

    public $incrementing = false;

    protected $primaryKey = ["property_id", "fields_segment_id"];

    protected $fillable = [
        "property_id",
        "fields_segment_id",
        "value",
        "reference_value",
    ];

    public function segment(): BelongsTo {
        return $this->belongsTo(FieldSegment::class, "fields_segment_id", "id");
    }
}
