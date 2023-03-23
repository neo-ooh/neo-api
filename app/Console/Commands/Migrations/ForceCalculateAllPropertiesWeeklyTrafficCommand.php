<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ForceCalculateAllPropertiesWeeklyTrafficCommand.php
 */

namespace Neo\Console\Commands\Migrations;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Neo\Jobs\Traffic\EstimateWeeklyTrafficFromMonthJob;
use Neo\Jobs\Traffic\PushPropertyTrafficJob;
use Neo\Modules\Properties\Models\Property;

class ForceCalculateAllPropertiesWeeklyTrafficCommand extends Command {
    protected $signature = 'one-off:monthlytraffic-to-weekly';

    protected $description = 'Write weekly traffic entries for all properties using monthly traffic';

    public function handle() {
        $properties = Property::all("actor_id")->pluck("actor_id");
        foreach ($properties as $propertyId) {
            $this->info("Dispatching for property #$propertyId");

            $jobs = [];
            foreach ([2019, Carbon::now()->year] as $year) {
                for ($i = 1; $i <= 12; $i++) {
                    $jobs[] = new EstimateWeeklyTrafficFromMonthJob($propertyId, $year, $i);
                }
            }

            Bus::batch($jobs)->then(fn() => PushPropertyTrafficJob::dispatch($propertyId));
        }
    }
}
