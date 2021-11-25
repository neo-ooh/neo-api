<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ForceCalculateAllPropertiesWeeklyTrafficCommand.php
 */

namespace Neo\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Neo\Jobs\Traffic\EstimateWeeklyTrafficFromMonthJob;
use Neo\Models\Property;

class ForceCalculateAllPropertiesWeeklyTrafficCommand extends Command {
    protected $signature = 'one-off:monthlytraffic-to-weekly';

    protected $description = 'Write weekly traffic entries for all properties using monthly traffic';

    public function handle() {
        $properties = Property::all("actor_id")->pluck("actor_id");
        foreach ($properties as $propertyId) {
            foreach ([2019, 2021] as $year) {
                for ($i = 1; $i <= 12; $i++) {
                    EstimateWeeklyTrafficFromMonthJob::dispatch($propertyId, $year, $i);
                }
            }
        }
    }
}
