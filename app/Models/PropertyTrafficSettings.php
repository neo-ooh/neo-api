<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @package Neo\Models
 * @property boolean                   $is_required
 * @property int                       $start_year
 * @property Date                      $grace_override
 * @property string                    $input_method
 * @property string                    $missing_value_strategy
 * @property int                       $placeholder_value
 *
 * @property int                       $property_id
 * @property Property                  $property
 * @property Collection<TrafficSource> $source
 */
class PropertyTrafficSettings extends Model {
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "property_traffic_settings";

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = "property_id";

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
        "is_required"    => "boolean",
        "grace_override" => "date"
    ];

    protected $with = ["data", "source"];

    protected $fillable = ["is_required", "start_year", "grace_override"];

    public function property() {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }

    public function data(): HasMany {
        return $this->hasMany(PropertyTraffic::class, "property_id", "property_id");
    }

    public function source(): BelongsToMany {
        return $this->belongsToMany(TrafficSource::class, "property_traffic_source", "property_id", "source_id")
                    ->withPivot("uid");
    }
}
