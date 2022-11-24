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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Neo\Enums\ProductsFillStrategy;
use Neo\Modules\Broadcast\Models\Campaign;

/**
 * @property-read int                 $id
 * @property-read int                 $uid
 * @property-read int                 $contract_id
 * @property string                   $name
 * @property Carbon                   $start_date
 * @property Carbon                   $end_date
 * @property string                   $type
 * @property-read Carbon              $created_at
 * @property-read Carbon              $updated_at
 *
 * @property Collection<ContractLine> $lines
 */
class ContractFlight extends Model {
    protected $table = "contracts_flights";

    protected $primaryKey = "id";

    protected $fillable = [
        "contract_id",
        "uid",
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

    public function reservations(): HasMany {
        return $this->hasMany(ContractReservation::class, "flight_id", "id");
    }

    public function contract(): BelongsTo {
        return $this->belongsTo(Contract::class, "contract_id", "id");
    }

    public function campaigns(): HasMany {
        return $this->hasMany(Campaign::class, "flight_id", "id");
    }

    public function getExpectedImpressionsAttribute() {
        return $this->lines()->whereHas('product', function (Builder $query) {
            $query->whereHas("category", function (Builder $query) {
                $query->where("fill_strategy", "=", ProductsFillStrategy::digital);
            });
        })->sum("impressions");
    }
}
