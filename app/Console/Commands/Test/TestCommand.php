<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Traffic\RollingTrafficCalculator;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     */
    public function handle() {
//        /** @var Collection<Property> $otgProperties */
//        $otgProperties = Property::query()
//                                 ->where("network_id", "=", 3)
//                                 ->get();
//
//        $doc = ProgrammaticExport::make($otgProperties->pluck("actor_id")->toArray());
//        $doc->build();
//        $doc->output(Storage::disk("local")->path("otg-export.xlsx"));

        $property = Property::find(640);
//        for ($i = 1; $i <= 12; $i++) {
//            (new EstimateWeeklyTrafficFromMonthJob($property->getKey(), $year, $i))->handle();
//        }

        $calculator = new RollingTrafficCalculator($property->traffic);
        dump($calculator->compute());
    }
}
