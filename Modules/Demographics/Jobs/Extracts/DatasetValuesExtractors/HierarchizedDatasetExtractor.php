<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HierarchizedDatasetExtractor.php
 */

namespace Neo\Modules\Demographics\Jobs\Extracts\DatasetValuesExtractors;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Demographics\Models\DatasetDatapoint;
use Override;

class HierarchizedDatasetExtractor implements DatasetValuesExtractor {
    #[Override] public static function extract(Connection $db, DatasetDatapoint $datapoint, int $geographyReportId, int $extractId) {
        $result = DB::connection("neo_demographics")->statement(/** @lang PostgreSQL */ <<<EOF
            INSERT INTO "extracts_values" ("extract_id", "datapoint_id", "value")
            SELECT
                ? AS "extract_id",
                "dp"."id" AS "datapoint_id",
                COALESCE(CASE
                    WHEN "dp"."type" = 'discrete' THEN
                        SUM("dv"."value" * "grv"."geography_weight") / SUM(NULLIF("dv"."reference_value", 0) * "grv"."geography_weight")
                    WHEN "dp"."type" = 'continuous' THEN
                        SUM("dv"."value") / SUM("grv"."geography_weight")
                    ELSE 0
                END, 1)
                AS "value"
            FROM "geographic_reports_values" "grv"
            INNER JOIN "datasets_values" "dv" ON "dv"."area_id" = "grv"."area_id"
            INNER JOIN "datasets_datapoints" "dp" ON "dp"."id" = "dv"."datapoint_id" AND "dp"."dataset_version_id" = 1
            WHERE "grv"."report_id" = ?
            AND "grv"."geography_weight" > 0
            AND "dp"."id" = ?
            GROUP BY "dp"."id", "dp"."code"
            ORDER BY "dp"."id";
            EOF
            , [$extractId, $geographyReportId, $datapoint->getKey()]);

        return [$datapoint->getKey(), $datapoint->code, $result];
    }
}
