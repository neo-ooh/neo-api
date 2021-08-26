<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyData.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $website
 * @property string $description_fr
 * @property string $description_en
 * @property int $stores_count
 * @property int $visit_length
 * @property int $average_income
 * @property bool $is_downtown
 * @property string $data_source
 * @property int $market_population
 * @property int $gross_area
 * @property int $spending_per_visit
 */
class PropertyData extends Model {
    protected $table = "properties_data";

    protected $primaryKey = "property_id";

    public $incrementing = false;

    public $timestamps = false;

    protected $touches = ["property"];

    protected $casts = [
        "is_downtown" => "boolean"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }
}
