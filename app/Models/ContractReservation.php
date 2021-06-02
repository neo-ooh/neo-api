<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ContractReservation
 *
 * @package Neo\Models
 *
 * @property integer $id
 * @property integer $contract_id
 * @property integer $external_id
 * @property string $network
 * @property string $name
 * @property string $original_name
 * @property Date $start_date
 * @property Date $end_date
 * @property Date $created_at
 * @property Date $updated_at
 */
class ContractReservation extends Model
{
    use HasFactory;

    protected $table = "contracts_reservations";

    protected $dates = [
        "start_date",
        "end_date"
    ];

    protected $fillable = [
        "contract_id",
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
}
