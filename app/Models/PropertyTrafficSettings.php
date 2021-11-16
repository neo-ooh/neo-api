<?php

namespace Neo\Models;

use Carbon\Carbon;
use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use stdClass;

/**
 * @package Neo\Models
 * @property boolean                            $is_required
 * @property int                                $start_year
 * @property Date                               $grace_override
 * @property string                             $input_method
 * @property string                             $missing_value_strategy
 * @property int                                $placeholder_value
 *
 * @property int                                $property_id
 * @property Property                           $property
 * @property Collection<TrafficSource>          $source
 * @property Collection<PropertyTrafficMonthly> $data
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

    protected $with = [];

    protected $fillable = ["is_required", "start_year", "grace_override"];

    public function property() {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }

    /**
     * Monthly traffic data points
     *
     * @return HasMany
     */
    public function data(): HasMany {
        return $this->hasMany(PropertyTrafficMonthly::class, "property_id", "property_id")->orderBy("year", 'desc');
    }

    /**
     * Weekly traffic data points
     *
     * @return HasMany
     */
    public function weekly_data(): HasMany {
        return $this->hasMany(PropertyTraffic::class, "property_id", "property_id");
    }

    public function source(): BelongsToMany {
        return $this->belongsToMany(TrafficSource::class, "property_traffic_source", "property_id", "source_id")
                    ->withPivot("uid");
    }


    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    /**
     * This methods fills the `monthly_traffic` attribute of the model as an array containing traffic monthly traffic data that
     * can be used to calculate impressions
     */
    public function loadMonthlyTraffic(?Province $province) {
        $monthly_traffic = new stdClass();
        $trafficData     = $this->data->sortBy(["year, month"], descending: true);
        $currentYear     = Carbon::now()->year;

        for ($monthIndex = 0; $monthIndex < 12; ++$monthIndex) {
            /** @var ?PropertyTrafficMonthly $trafficEntry */
            $trafficEntry = $trafficData->where("month", "=", $monthIndex)->first();

            // Is there a traffic entry for the month ?
            if ($trafficEntry === null) {
                // No, do we have a default value we can use ?
                if ($this->missing_value_strategy === 'USE_PLACEHOLDER') {
                    // Yes, use it
                    $monthly_traffic->$monthIndex = $this->placeholder_value;
                    continue;
                }

                // No data available, and no default value, set traffic as 0
                $monthly_traffic->$monthIndex = 0;
                continue;
            }

            // We have a traffic value. Is it for the current year ?
            if ($trafficEntry->year === $currentYear) {
                // This traffic data is for the current year, we can use it without any change
                $monthly_traffic->$monthIndex = $trafficEntry->final_traffic;
                continue;
            }

            // The traffic value is from a previous year, as of now (2021-09) we need to adjust the traffic value in order to use it
            // If a default value is available, we will prefer using that
            if ($this->missing_value_strategy === 'USE_PLACEHOLDER') {
                $monthly_traffic->$monthIndex = $this->placeholder_value;
                continue;
            }

            // No default value, we have to apply corrections based on the province
            $coef                         = $province?->slug === 'QC' ? '.75' : '.65';
            $monthly_traffic->$monthIndex = round($trafficEntry->final_traffic * $coef);
        }

        $this->monthly_traffic = $monthly_traffic;
    }


    public function getWeeklyTrafficAttribute() {
        return $this->weekly_data->groupBy("year")
                                 ->map(fn($points) => $points->mapWithKeys(fn($point) => [$point->week => $point->traffic]));
    }
}
