<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldSegmentStats.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $value_count
 * @property int $min_index
 * @property int $max_index
 */
class FieldSegmentStats extends Model {
    protected $table = "fields_segments_stats";
    protected $primaryKey = "id";
}
