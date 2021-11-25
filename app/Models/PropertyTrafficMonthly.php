<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Gate;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * Class Property Traffic
 *
 * @property int      $property_id
 * @property int      $year
 * @property int      $month 0-indexed month
 * @property int      $traffic
 * @property int      $temporary
 * @property int      $final_traffic
 *
 * @property Property $property
 */
class PropertyTrafficMonthly extends Model {
    use HasCompositePrimaryKey;
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
    protected $table = "properties_traffic_monthly";


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = ["property_id", "year", "month"];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    public $fillable = [
        "property_id",
        "year",
        "month",
        "traffic",
        "temporary",
    ];

    protected $casts = [
        "year"          => "integer",
        "month"         => "integer",
        "traffic"       => "integer",
        "temporary"     => "integer",
        "final_traffic" => "integer",
    ];

    protected static function boot() {
        parent::boot();

        static::retrieved(function (PropertyTrafficMonthly $traffic) {
            $traffic->makeHiddenIf(!Gate::allows(\Neo\Enums\Capability::properties_edit), ["temporary"]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }
}
