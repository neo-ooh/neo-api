<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GenerateIndexSetsJob.php
 */

namespace Neo\Modules\Demographics\Jobs\IndexSets;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use JsonException;
use Neo\Modules\Demographics\Jobs\DemographicJobBase;
use Neo\Modules\Demographics\Models\DemographicProperty;
use Neo\Modules\Demographics\Models\Enums\ReportStatus;
use Neo\Modules\Demographics\Models\IndexSet;
use Neo\Modules\Demographics\Models\IndexSetTemplate;
use Neo\Modules\Demographics\Models\StructuredColumns\IndexSetMetadata;
use Neo\Modules\Properties\Models\Property;

/**
 * Generates index sets for all properties that fits the required criteria for generation.
 * The job can run for a single, specific, template if the desired template is provided to the constructor
 */
class GenerateIndexSetsJob extends DemographicJobBase {

    public function __construct(protected readonly IndexSetTemplate|null $template = null) {
    }

    public function run(): mixed {
        // If a template was provided, use this one exclusively
        if ($this->template !== null) {
            $this->generateSetsForTemplate($this->template);
            return true;
        }

        // List and use all templates
        $templates = IndexSetTemplate::query()->lazy(5);
        foreach ($templates as $template) {
            $this->generateSetsForTemplate($template);
        }

        // Done
        return true;
    }

    /**
     * @throws JsonException
     */
    protected function generateSetsForTemplate(IndexSetTemplate $template) {
        // For an index set to be generated for a property, we need
        // 1. A property that doesn't already have one for the current template
        // 2. A property that has extracts for both the primary and reference extract templates specified in the index set template

        // List properties missing index sets
        $properties = Property::query()
            ->whereDoesntHave("index_sets", function (Builder $query) use ($template) {
                $query->where("template_id", "=", $template->getKey());
            })
            ->lazy(250)
            ->chunk(250);

        $indexSets = [];

        /** @var Collection<Property> $propertiesChunk */
        foreach ($properties as $propertiesChunk) {
            // From the list of properties missing the index set, list the ones that have the proper resources to generate them
            $properties = DemographicProperty::query()
                ->whereHas("extracts", function (Builder $query) use ($template) {
                    $query->where("template_id", "=", $template->primary_extract_template_id)
                        ->where("status", "=", ReportStatus::Done);
                })
                ->whereHas("extracts", function (Builder $query) use ($template) {
                    $query->where("template_id", "=", $template->reference_extract_template_id)
                        ->where("status", "=", ReportStatus::Done);
                })
                ->whereIn("id", $propertiesChunk->pluck("actor_id"))
                ->with(["extracts" => function ($query) {
                    $query->where("status", "=", ReportStatus::Done);
                }])
                ->get();

            // Describe the index sets to generate
            /** @var DemographicProperty $property */
            foreach ($properties as $property) {
                $indexSets[] = [
                    "template_id"          => $template->getKey(),
                    "property_id"          => $property->getKey(),
                    "primary_extract_id" => $property->extracts->firstWhere("template_id", "=", $template->primary_extract_template_id)->getKey(),
                    "reference_extract_id" => $property->extracts->firstWhere("template_id", "=", $template->reference_extract_template_id)->getKey(),
                    "metadata"             => IndexSetMetadata::from([])->toJson(),
                    "status"               => ReportStatus::Pending,
                    "requested_at"         => Carbon::now(),
                ];
            }
        }

        IndexSet::query()->insert($indexSets);
    }
}
