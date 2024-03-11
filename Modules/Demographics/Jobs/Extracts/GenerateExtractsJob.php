<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GenerateExtractsJob.php
 */

namespace Neo\Modules\Demographics\Jobs\Extracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Models\DemographicProperty;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Modules\Demographics\Models\ExtractTemplate;
use Neo\Modules\Demographics\Models\StructuredColumns\ExtractMetadata;
use function Ramsey\Uuid\v4;

/**
 * Generates extracts for all properties that fits the required criteria for generation for all extracts.
 * The job can run for a single, specific, template if the desired template is provided to the constructor
 */
class GenerateExtractsJob extends DemographicJobBase {

    public function __construct(protected readonly ExtractTemplate|null $template = null) {
    }

    public function run(): mixed {
        // If a template was provided, use this one exclusively
        if ($this->template !== null) {
            $this->generateExtractsForTemplate($this->template);
            return true;
        }

        // List and use all templates
        $templates = ExtractTemplate::query()->lazy(5);
        foreach ($templates as $template) {
            $this->generateExtractsForTemplate($template);
        }

        // Done
        return true;
    }

    protected function generateExtractsForTemplate(ExtractTemplate $template) {
        // For a report to be generated for a property, we need
        // 1. A property that doesn't already have one for the current template
        // 2. A property that has a geographic report for the template attached with the extract template

        $validateGeographicReport = static function ($query) use ($template) {
            $query->where("template_id", "=", $template->geographic_report_template_id)
                  ->where("status", "=", ReportStatus::Done);

        };

        $properties = DemographicProperty::query()
                                         ->whereHas("geographic_reports", $validateGeographicReport)
                                         ->whereDoesntHave("extracts", function (Builder $query) use ($template) {
                                             $query->where("template_id", "=", $template->getKey())
                                                   ->whereIn("status", [ReportStatus::Pending, ReportStatus::Active, ReportStatus::Done]);
                                         })
            // Only load the geographic reports that matched the previous criteria
                                         ->with(["geographic_reports" => $validateGeographicReport])
                                         ->lazy(250);

        $extracts = [];

        /** @var DemographicProperty $property */
        foreach ($properties as $property) {
            $extracts[] = [
                "uuid"                 => v4(),
                "template_id"          => $template->getKey(),
                "property_id"          => $property->getKey(),
                "geographic_report_id" => $property->geographic_reports->first()->id,
                "metadata"             => ExtractMetadata::from([])->toJson(),
                "status"               => ReportStatus::Pending,
                "requested_at"         => Carbon::now(),
            ];
        }

        Extract::query()->insert($extracts);
    }
}
