<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * Class Property Traffic
 *
 * @property int      $property_id
 * @property int      $year
 * @property int      $month
 * @property int      $traffic
 *
 * @property Property $property
 */
class PropertyTraffic extends Model {
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
    protected $table = "properties_traffic";


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
        "traffic"
    ];

    protected $casts = [
        "year" => "integer",
        "month" => "integer",
        "traffic" => "integer",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }
}