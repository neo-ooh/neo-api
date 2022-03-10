<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicValue.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $id
 * @property int    $property_id
 * @property string $value_id
 * @property double $value
 * @property double $reference_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DemographicValue extends Model {
    protected $table = "demographic_values";

    protected $primaryKey = "id";

    public function variable(): BelongsTo {
        return $this->belongsTo(DemographicVariable::class, "value_id", "id");
    }

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }
}
