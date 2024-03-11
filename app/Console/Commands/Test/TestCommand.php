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

use GeoJson\Geometry\MultiPolygon;
use Illuminate\Console\Command;
use Neo\Modules\Demographics\Jobs\GeographicReports\Processors\IsochroneAreaProcessor;
use Neo\Modules\Properties\Models\Property;
use Neo\Services\Isochrone\IsochroneAdapter;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
    protected $signature = 'test:test {--start=} {--step=}';

    protected $description = 'Internal tests';

    /**
     * @return void
     * @throws Exception
     */
    public function handle() {
//        $template = GeographicReportTemplate::query()->find(5);
//        $j = new GenerateGeographicReportsJob($template);
//        $j->handle();

//        $report = GeographicReport::find(13);
//        $report->status = ReportStatus::Pending;
//        $j = new ProcessGeographicReportJob($report);
//        $j->handle();

//        $extract         = Extract::query()->find(6);
//        $extract->status = ReportStatus::Pending;
//        $j               = new ProcessExtractJob($extract);
//        $j->handle();

//        $set         = IndexSet::query()->find(2);
//        $set->status = ReportStatus::Pending;
//        $j               = new ProcessIndexSetJob($set);
//        $j->handle();

//        $j = new GenerateExtractsJob();
//        $j->handle();

//        $j = new GenerateIndexSetsJob();
//        $j->handle();

//
//        $step = $this->option("step");
//
//        for ($i = $this->option("start"); $i < 1_114_000; $i += $step) {
//            $this->info("$i -> " . $i + $step . "...");
//
//            DB::connection("neo_demographics")
//              ->update(<<<EOF
//                UPDATE "datasets_values"
//                SET "reference_value" = "refs"."value"
//                FROM (
//                    SELECT dp.id AS datapoint_id, dv.area_id AS area_id, drv.value AS "value"
//                    FROM datasets_values dv
//                    JOIN datasets_datapoints dp ON dp.id = dv.datapoint_id
//                    LEFT JOIN datasets_values drv ON drv.datapoint_id = dp.reference_datapoint_id AND dv.area_id = drv.area_id
//                ) AS refs
//                WHERE datasets_values.datapoint_id = refs.datapoint_id AND datasets_values.area_id = refs.area_id
//                AND datasets_values.area_id BETWEEN ? AND ?
//            EOF
//                  , [$i, $i + $step]);

        $tt = app()->make(IsochroneAdapter::class);
        $p  = Property::query()->find(31);

        /** @var MultiPolygon $isochrone */
//        $isochrone = $tt->getIsochrone(
//            lng: $p->address->geolocation->longitude,
//            lat: $p->address->geolocation->latitude,
//            durationMin: 90,
//            travelMethod: 'driving'
//        );

        $processor = new IsochroneAreaProcessor($p->address->geolocation, 90, 'driving');

//        dump(count($processor->getEntries();
    }
}
