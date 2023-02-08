<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldSegment.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int    $id
 * @property int    $field_id
 * @property string $name_en
 * @property string $name_fr
 * @property int    $order
 * @property string $color
 * @property string $variable_id
 * @property Carbon $created_at
 * @property Carbon $update_at
 */
class FieldSegment extends Model {
    protected $table = "fields_segments";
    protected $primaryKey = "id";
    protected $fillable = [
        "field_id",
        "name_en",
        "name_fr",
        "order",
        "color",
        "variable_id",
    ];

    public function stats(): HasOne {
        return $this->hasOne(FieldSegmentStats::class, "id", "id");
    }

    public function field(): BelongsTo {
        return $this->belongsTo(Field::class, "field_id", "id");
    }
}
