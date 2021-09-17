<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Neo\Models\Odoo\Property as OdooProperty;
use Neo\Rules\AccessibleProperty;

/**
 * Class Property
 *
 * @property int                     $actor_id
 * @property int                     $address_id
 * @property int                     $network_id
 * @property Date                    $created_at
 * @property Date                    $updated_at
 *
 * @property Actor                   $actor
 * @property PropertyTrafficSettings $traffic
 * @property Address|null            $address
 * @property PropertyData            $data
 * @property OdooProperty            $odoo
 * @property Network                 $network
 */
class Property extends SecuredModel {
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "properties";


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = "actor_id";

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    public $casts = [
        "require_traffic"        => "boolean",
        "traffic_grace_override" => "date"
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleProperty::class;

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "actor_id");
    }

    public function network(): BelongsTo {
        return $this->belongsTo(Network::class, "network_id");
    }

    public function traffic(): HasOne {
        return $this->hasOne(PropertyTrafficSettings::class, "property_id", "actor_id");
    }

    public function address(): BelongsTo {
        return $this->belongsTo(Address::class, "address_id", "id");
    }

    public function odoo() {
        return $this->hasOne(OdooProperty::class, "property_id", "actor_id");
    }

    public function data() {
        return $this->hasOne(PropertyData::class, "property_id", "actor_id");
    }


    /*
    |--------------------------------------------------------------------------
    |
    |--------------------------------------------------------------------------
    */

    public function getTraffic(int $year, int $month): int|null {
        /** @var ?PropertyTraffic $traffic */
        $traffic = $this->traffic->data
            ->where("year", "=", $year)
            ->where("month", "=", $month)
            ->first();

        if (!$traffic) {
            return null;
        }

        return $traffic->final_traffic;
    }
}
