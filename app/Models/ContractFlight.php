<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractFlight.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read int    $id
 * @property-read int    $contract_id
 * @property string      $name
 * @property Carbon      $start_date
 * @property Carbon      $end_date
 * @property string      $type
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class ContractFlight extends Model {
    protected $table = "contracts_flights";

    protected $primaryKey = "id";

    protected $fillable = [
        "contract_id",
        "name",
        "start_date",
        "end_date",
        "type",
    ];

    protected $dates = [
        "start_date",
        "end_date",
    ];

    public function lines(): HasMany {
        return $this->hasMany(ContractLine::class, "flight_id", "id");
    }
}
