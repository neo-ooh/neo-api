<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyFieldSegmentValue.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * @property int    $property_id
 * @property int    $fields_segment_id
 * @property double $value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PropertyFieldSegmentValue extends Model {
    use HasCompositePrimaryKey;

    protected $table = "properties_fields_segments_values";
    protected $primaryKey = ["property_id", "fields_segment_id"];
    protected $fillable = [
        "property_id",
        "field_segment_id",
        "value"
    ];

    public function segment(): BelongsTo {
        return $this->belongsTo(FieldSegment::class, "fields_segment_id", "id");
    }
}
