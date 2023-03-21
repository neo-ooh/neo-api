<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyTrafficSettings.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Neo\Modules\Properties\Enums\TrafficFormat;
use Neo\Modules\Properties\Traffic\RollingTrafficCalculator;

/**
 * @package Neo\Models
 * @property TrafficFormat                   $format
 * @property boolean                         $is_required
 * @property int                             $start_year
 * @property Date                            $grace_override
 * @property string                          $input_method
 * @property string                          $missing_value_strategy
 * @property int                             $placeholder_value
 *
 * @property int                             $property_id
 * @property Property                        $property
 * @property Collection<TrafficSource>       $source
 * @property Collection<MonthlyTrafficDatum> $data
 *
 * @property Collection<WeeklyTrafficDatum>  $weekly_data
 * @property \Illuminate\Support\Collection  $weekly_traffic
 */
class PropertyTrafficSettings extends Model {
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
     * @var array<string, string>
     */
    public $casts = [
        "is_required"    => "boolean",
        "grace_override" => "date",
        "format"         => TrafficFormat::class,
    ];

    protected $with = [];

    protected $fillable = [
        "format",
        "is_required",
        "start_year",
        "grace_override",
    ];

    public function property(): BelongsTo {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }

    /**
     * Monthly traffic data points
     *
     * @return HasMany
     * @deprecated Use `monthlyData` relation instead.
     */
    public function data(): HasMany {
        return $this->hasMany(MonthlyTrafficDatum::class, "property_id", "property_id")->orderBy("year", 'desc');
    }

    /**
     * Monthly traffic data
     *
     * @return HasMany<MonthlyTrafficDatum>
     */
    public function monthly_data(): HasMany {
        return $this->hasMany(MonthlyTrafficDatum::class, "property_id", "property_id")
                    ->orderBy("year", "desc");
    }

    /**
     * Weekly traffic data points
     *
     * @return HasMany<WeeklyTrafficDatum>
     */
    public function weekly_data(): HasMany {
        return $this->hasMany(WeeklyTrafficDatum::class, "property_id", "property_id")
                    ->orderBy("year")
                    ->orderBy("week");
    }

    /**
     *
     *
     * @return BelongsToMany<TrafficSource>
     */
    public function source(): BelongsToMany {
        return $this->belongsToMany(TrafficSource::class, "property_traffic_source", "property_id", "source_id")
                    ->withPivot("uid");
    }


    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */


    public function getRollingWeeklyTrafficCacheKey(): string {
        return "property-$this->property_id-rolling-weekly-traffic";
    }

    public function getRollingWeeklyTrafficAttribute() {
        return $this->getRollingWeeklyTraffic();
    }

    /**
     * This method returns an array of 53 values corresponding to the weekly traffic for the property for a year.
     *
     * @return array
     */
    public function getRollingWeeklyTraffic(): array {
        return Cache::remember($this->getRollingWeeklyTrafficCacheKey(), 3600 * 24, function () {
            $calculator = new RollingTrafficCalculator($this);
            return $calculator->compute();
        });
    }
}
