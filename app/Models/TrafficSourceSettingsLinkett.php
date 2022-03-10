<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficSourceSettingsLinkett.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @package Neo\Models
 * @property string        $api_key
 *
 * @property TrafficSource $source
 *
 * @property int           $source_id
 */
class TrafficSourceSettingsLinkett extends Model {
    use HasFactory;

    protected $table = "traffic_source_settings_linkett";

    protected $primaryKey = "source_id";

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ["api_key"];

    public function source(): BelongsTo {
        return $this->belongsTo(TrafficSource::class, "source_id", "id");
    }
}
