<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullPropertyTraffic.php
 */

namespace Neo\Console\Commands;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Neo\Models\Property;
use Neo\Models\PropertyTrafficMonthly;
use Neo\Services\Traffic\Traffic;

class PullPropertyTraffic extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property:pull-traffic {property}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull the available traffic data from the configured source';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        /** @var Property $property */
        $property = Property::query()->findOrFail($this->argument("property"));

        // Make sure the property has a data source configure
        if ($property->traffic->input_method === 'MANUAL') {
            $this->output->error("This property has no data source configured.");
            return -1;
        }

        $source = $property->traffic->source->first();

        $period = CarbonPeriod::since(Carbon::create($property->traffic->start_year))->month()->until("now");

        $trafficSource = Traffic::from($source);

        /** @var Carbon $month */
        foreach ($period as $month) {
            // Ignore the year 2020, as well as the current month
            if ($month->year === 2020 || $month->isCurrentMonth()) {
                continue;
            }

            $start = $month->startOfMonth();
            $end   = $month->copy()->addMonth();


            // Fetch the value
            $traffic = $trafficSource->getTraffic($property, $start, $end);

            // Print the output
            $this->output->comment($month->format("Y-m") . " - " . $traffic);

            // If the traffic value is 0 and there is already a record, we ignore it
            if ($traffic === 0 && PropertyTrafficMonthly::query()->where([
                    "property_id" => $property->actor_id,
                    "year"        => $start->year,
                    "month"       => $start->month - 1
                ])->exists()) {
                continue;
            }

            // Save the value
            PropertyTrafficMonthly::query()->updateOrCreate([
                "property_id" => $property->actor_id,
                "year"        => $start->year,
                "month"       => $start->month - 1,
            ], [
                "traffic" => $traffic,
            ]);

            // Wait in-between fetched or we get a 503 from Linkett
        }

        // We're good
        return 0;
    }
}
