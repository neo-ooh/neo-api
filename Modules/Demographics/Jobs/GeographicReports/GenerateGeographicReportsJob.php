<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GenerateGeographicReportsJob.php
 */

namespace Neo\Modules\Demographics\Jobs\GeographicReports;

use Illuminate\Database\Eloquent\Builder;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Models\Enums\GeographicReportTemplateTargetingType;
use Neo\Modules\Demographics\Models\GeographicReport;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;
use Neo\Modules\Demographics\Models\StructuredColumns\GeographicReportTemplateConfiguration;
use Neo\Modules\Properties\Models\Property;

class GenerateGeographicReportsJob extends DemographicJobBase {
    public function __construct(protected readonly GeographicReportTemplate|null $template = null) {
    }

    public function run(): mixed {
        if($this->template !== null) {
            $this->generateGeographicReportsForTemplate($this->template);
        }

        $templates = GeographicReportTemplate::query()->lazy(5);
        foreach ($templates as $template) {
            $this->generateGeographicReportsForTemplate($template);
        }

        // Done
        return true;
    }

    protected function generateGeographicReportsForTemplate(GeographicReportTemplate $template) {
        // We want to generate reports for all properties that match one of the configuration of the template,
        // that don't already have a report for this template.

        // First, validate the report format, as only Geographic Reports for Areas can be automatically generated

        // 1. For each configuration, we list all the properties that match it.

        /**
         * @var array<int, int> Map each property ID with the index of the configuration is matched with
         */
        $propertiesConfiguration = [];

        $configBlocksIndexes = $template->configuration
            ->toCollection()
            ->mapWithKeys(fn(GeographicReportTemplateConfiguration $config, $i) => [$i => $config->weight])
            ->sortDesc();

        foreach ($configBlocksIndexes as $i => $weight) {
            /** @var GeographicReportTemplateConfiguration $configBlock */
            $configBlock = $template->configuration[$i];

            $baseQuery = Property::query();
            // TODO: Add filtering based on property archival status

            $query = match ($configBlock->targeting) {
                GeographicReportTemplateTargetingType::all      => $baseQuery,
                GeographicReportTemplateTargetingType::Network  => $baseQuery->whereIn("network_id", $configBlock->target_ids),
                GeographicReportTemplateTargetingType::Market   => $baseQuery->whereHas("address", function (Builder $query) use ($configBlock) {
                    $query->whereHas("city", function (Builder $query) use ($configBlock) {
                        $query->whereIn("market_id", $configBlock->target_ids);
                    });
                }),
                GeographicReportTemplateTargetingType::city     => $baseQuery->whereHas("address", function (Builder $query) use ($configBlock) {
                    $query->whereIn("city_id", $configBlock->target_ids);
                }),
                GeographicReportTemplateTargetingType::tag      => $baseQuery->whereHas("actor", function (Builder $query) use ($configBlock) {
                    $query->whereHas("tags", function (Builder $query) use ($configBlock) {
                        $query->whereIn("id", $configBlock->target_ids);
                    });
                }),
                GeographicReportTemplateTargetingType::property => $baseQuery->whereIn("actor_id", $configBlock->target_ids),
            };

            $propertyIds = $query->pluck("actor_id")->toArray();

            // Assign configuration to property whose ids is not already referenced
            foreach ($propertyIds as $propertyId) {
                if (!isset($propertiesConfiguration[$propertyId])) {
                    $propertiesConfiguration[$propertyId] = $i;
                }
            }
        }

        // Now, to generate the reports, we still have to make sure the property doesn't already have one
        // We flip the array as searches on keys are faster than on values
        $propertyIdsWithReport = array_flip(GeographicReport::query()
                                                            ->where("template_id", "=", $template->getKey())
                                                            ->pluck("property_id")
                                                            ->toArray());

        foreach ($propertiesConfiguration as $propertyId => $configIndex) {
            if(isset($propertyIdsWithReport[$propertyId])) {
                // A report already exist, ignore
                continue;
            }

            // Generate and store the report. It will be processed later.
            $report = GeographicReport::fromTemplate($template, $configIndex);
            $report->property_id = $propertyId;
            $report->save();

            // Also, register the property id, just for consistency
            $propertyIdsWithReport[$propertyId] = $propertyId;
        }

        // All done.

        return true;
    }
}
