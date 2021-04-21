<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Client
 *
 * @package Neo\Models
 *
 * @property integer $id
 * @property integer $broadsign_customer_id
 * @property string name
 * @property Date $created_at
 * @property Date $updated_at
 *
 * @property Collection $contracts
 */
class Client extends Model
{
    use HasFactory;

    protected $table = "clients";

    protected $fillable = [
        "broadsign_customer_id",
        "name"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function contracts(): HasMany {
        return $this->hasMany(Contract::class, "contract_id", "id");
    }
}
