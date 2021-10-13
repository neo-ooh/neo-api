<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldSegment.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $field_id
 * @property string $name_en
 * @property string $name_fr
 * @property int $order
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
        "order"
    ];

    public function field() {
        return $this->belongsTo(Field::class, "field_id", "id");
    }
}
