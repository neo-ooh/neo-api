<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicVariable.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $label
 * @property string $provider
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class DemographicVariable extends Model {
    protected $table = "demographic_variables";

    protected $primaryKey = "id";

    public $incrementing = false;
}
