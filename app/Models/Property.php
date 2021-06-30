<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Rules\AccessibleActor;
use Neo\Rules\AccessibleProperty;

/**
 * Class Property
 *
 * @property int        $actor_id
 * @property boolean    $require_traffic
 * @property int        $traffic_start_year
 * @property Date       $traffic_grace_override
 * @property Date       $created_at
 * @property Date       $updated_at
 *
 * @property Actor      $actor
 * @property Collection $traffic_data
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

    public function traffic_data(): HasMany {
        return $this->hasMany(PropertyTraffic::class, "property_id", "actor_id");
    }
}
