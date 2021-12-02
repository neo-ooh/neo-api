<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImpressionsModel.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $start_month 1-indexed start month for the model
 * @property int    $end_month   1-indexed end month for the model
 * @property string $formula
 * @property array  $variables
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ImpressionsModel extends Model {

    protected $table = "impressions_models";

    protected $primaryKey = "id";

    protected $casts = [
        "variables" => "array"
    ];

    protected $fillable = [
        "start_month",
        "end_month",
        "formula",
        "variables",
    ];
}
