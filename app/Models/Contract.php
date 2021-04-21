<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Collection\Collection;

/**
 * Class Contract
 *
 * @package Neo\Models
 *
 * @property integer $id
 * @property string $contract_id // ID of the contract has set by sales (not related to the actual ID of the contract inside Connect)
 * @property integer $client_id
 * @property integer $owner_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Client $client
 * @property Actor $owner
 * @property Collection<ContractBurst> $bursts
 * @property Collection<ContractReservation> $reservations
 */
class Contract extends Model
{
    use HasFactory;

    protected $table = "contracts";

    protected $fillable = [
        "contract_id",
        "client_id",
        "owner_id"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class, "client_id", "id");
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, "owner_id", "id");
    }

    public function bursts(): HasMany {
        return $this->hasMany(ContractBurst::class, "contract_id", "id");
    }

    public function reservations(): HasMany {
        return $this->hasMany(ContractReservation::class, "contract_id", "id");
    }
}
