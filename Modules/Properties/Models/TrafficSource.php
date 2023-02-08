<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficSource.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @package Neo\Models
 * @property string                       $type
 * @property string                       $name
 * @property Date                         $created_at
 * @property Date                         $update_at
 *
 * @property TrafficSourceSettingsLinkett $settings
 *
 * @property int                          $id
 */
class TrafficSource extends Model {
    protected $table = "traffic_sources";

    protected $primaryKey = "id";

    public function settings(): HasOne {
        return $this->hasOne(TrafficSourceSettingsLinkett::class, "source_id", "id");
    }
}
