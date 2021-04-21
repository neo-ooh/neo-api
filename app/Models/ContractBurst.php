<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ContractBurst
 *
 * @package Neo\Models
 *
 * @property integer $id
 * @property integer $contract_id
 * @property integer $reservation_id
 * @property ?integer $actor_id
 * @property integer $location_id
 * @property Date $start_at
 * @property string $status
 * @property int $scale_percent
 * @property int $duration_ms
 * @property int $frequency_ms
 * @property Date $created_at
 * @property Date $updated_at
 *
 * @property Contract $contract
 * @property ContractReservation $reservation
 * @property Actor $actor
 * @property Location $location
 */
class ContractBurst extends Model
{
    use HasFactory;

    protected $table = "contracts_bursts";

    protected $dates = [
        "start_at"
    ];

    protected $fillable = [
        "contract_id",
        "actor_id",
        "player_id",
        "start_at",
        "status",
        "scale_percent",
        "duration_ms",
        "frequency_ms"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function contract(): BelongsTo {
        return $this->belongsTo(Contract::class, "contract_id", "id");
    }

    public function reservation(): BelongsTo {
        return $this->belongsTo(ContractReservation::class, "reservation_id", "id")->orderBy("name");
    }

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "actor_id", "id");
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class, "player_id", "id");
    }

    public function screenshots(): HasMany {
        return $this->hasMany(ContractScreenshot::class, "burst_id", "id")->orderBy("created_at");
    }
}
