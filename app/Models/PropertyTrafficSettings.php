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
 *
 * @property Collection<PropertyTraffic>        $weekly_data
 * @property \Illuminate\Support\Collection     $weekly_traffic
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
        return $this->hasMany(PropertyTraffic::class, "property_id", "property_id")
                    ->orderBy("year")
                    ->orderBy("week");
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
    public function getMonthlyTraffic(?Province $province) {
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

        return $monthly_traffic;
    }


    public function getWeeklyTrafficAttribute(): \Illuminate\Support\Collection {
        return $this->weekly_data->groupBy("year")
                                 ->map(fn($points) => $points->mapWithKeys(fn($point) => [$point->week => $point->traffic]))
                                 ->sortKeys(SORT_NUMERIC, "desc");
    }

    public function getRollingWeeklyTraffic(): array {
        if ($this->property->network_id === 1) {
            return $this->getShoppingRollingWeeklyTraffic();
        }

        $rollingTraffic = [];
        $trafficData    = $this->weekly_traffic;

        $yearTrafficIt = $trafficData->getIterator();

        $validData      = $this->weekly_data->where("traffic", "!==", 0);
        $propertyMedian = $validData->count() > 0
            ? $validData->where("traffic", "!==", 0)->pluck("traffic")->sum() / $validData->count()
            : 0;

        for ($week = 1; $week <= 53; $week++) {
            $yearTrafficIt->rewind();
            $weekTraffic    = 0;
            $weekComponents = 0;

            do {
                $t = $yearTrafficIt->current()[$week] ?? 0;

                if ($t !== 0) {
                    $weekTraffic    += $t;
                    $weekComponents += 1;
                }

                $yearTrafficIt->next();
            } while ($yearTrafficIt->valid());

            if ($weekComponents > 0) {
                $rollingTraffic[$week] = round($weekTraffic / $weekComponents);
                continue;
            }

            if ($this->missing_value_strategy === 'USE_PLACEHOLDER') {
                $weekTraffic = $this->placeholder_value / 4;
            } else {
                $weekTraffic = $propertyMedian;
            }

            $rollingTraffic[$week] = round($weekTraffic);
        }

        return $rollingTraffic;
    }

    protected function getShoppingRollingWeeklyTraffic(): array {
        $rollingTraffic = [];
        // We select the most recent entry with a positive traffic and who is not the 53rd week because this one is tricky
        $mostRecentDatum = $this->weekly_data->last(fn($datum) => $datum->traffic > 0 && $datum->week !== 53);

        if (!$mostRecentDatum) {
            // Return an empty array if no values at all
            for ($week = 1; $week <= 53; $week++) {
                $rollingTraffic[$week] = 0;
            }
            return $rollingTraffic;
        }

        $referenceDatum = $this->weekly_data->first(
            fn($datum) => $datum->year === $this->start_year && $datum->week === $mostRecentDatum->week
        );

        if (!$referenceDatum) {
            for ($week = 1; $week <= 53; $week++) {
                $rollingTraffic[$week] = 0;
            }
            return $rollingTraffic;
        }

        $evol = $mostRecentDatum->traffic / $referenceDatum->traffic;

        for ($week = 1; $week <= 53; $week++) {

            /** @var PropertyTraffic|null $mostRecentDatumForPeriod */
            $mostRecentDatumForPeriod = $this->weekly_data->where("week", "=", $week)
                                                          ->sortBy("year", SORT_REGULAR, "desc")
                                                          ->first();

            /** @var PropertyTraffic|null $referenceDatumForPeriod */
            $referenceDatumForPeriod = $this->weekly_data->first(fn($datum) => $datum->year === $this->start_year && $datum->week === $week);

            // If the two data are the same, directly apply the factored-down result
            if ($mostRecentDatumForPeriod && $referenceDatumForPeriod &&
                $mostRecentDatumForPeriod->year === $referenceDatumForPeriod->year &&
                $mostRecentDatumForPeriod->week === $referenceDatumForPeriod->week) {
                $rollingTraffic[$week] = round($referenceDatumForPeriod->traffic * $evol);
                continue;
            }

            $rollingTraffic[$week] = round(max(($referenceDatumForPeriod->traffic ?? 0) * $evol, ($mostRecentDatumForPeriod->traffic ?? 0)));
        }

        return $rollingTraffic;
    }
}
