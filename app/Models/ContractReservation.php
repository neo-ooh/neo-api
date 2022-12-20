<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractReservation.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

/**
 * Class ContractReservation
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property int    $contract_id
 * @property int    $flight_id
 * @property int    $broadcaster_id
 * @property string $external_id
 * @property string $network
 * @property string $name
 * @property string $original_name
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContractReservation extends Model {
    protected $table = "contracts_reservations";

    protected $dates = [
        "start_date",
        "end_date",
    ];

    protected $fillable = [
        "contract_id",
        "broadcaster_id",
        "external_id",
        "network",
        "name",
        "original_name",
        "start_date",
        "end_date",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function contract(): BelongsTo {
        return $this->belongsTo(Contract::class, "contract_id");
    }

    public function flight(): BelongsTo {
        return $this->belongsTo(ContractFlight::class, "flight_id");
    }

    public function broadcaster(): BelongsTo {
        return $this->belongsTo(ContractFlight::class, "flight_id");
    }

    public function toResource(): ExternalBroadcasterResourceId {
        return new ExternalBroadcasterResourceId(
            broadcaster_id: $this->broadcaster_id,
            external_id   : $this->external_id,
            type          : ExternalResourceType::Campaign,
        );
    }
}
