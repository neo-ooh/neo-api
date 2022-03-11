<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Field.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int      $id
 * @property int|null $category_id
 * @property string   $name_en
 * @property string   $name_fr
 * @property string   $type One of 'int', 'float' or 'bool'
 * @property string   $unit
 * @property bool     $is_filter
 * @property Carbon   $created_at
 * @property Carbon   $update_at
 */
class Field extends Model {
    protected $primaryKey = "id";

    protected $table = "fields";

    protected $fillable = [
        "category_id",
        "name_en",
        "name_fr",
        "type",
        "unit",
        "is_filter"
    ];

    protected $casts = [
        "is_filter" => "boolean"
    ];

    protected $with = ["segments"];

    public function segments(): HasMany {
        return $this->hasMany(FieldSegment::class, "field_id", "id")->orderBy("order");
    }

    public function category(): BelongsTo {
        return $this->belongsTo(FieldsCategory::class, "category_id", "id");
    }
}
