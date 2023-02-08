<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DemographicVariable.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $label
 * @property string $provider
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DemographicVariable extends Model {
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "demographic_variables";

    /**
     * The primary key for the table
     *
     * @var string
     */
    protected $primaryKey = "id";

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
}
