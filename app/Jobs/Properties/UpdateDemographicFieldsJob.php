<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateDemographicFieldsJob.php
 */

namespace Neo\Jobs\Properties;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Neo\Models\DemographicValue;
use Neo\Models\Field;
use Neo\Models\FieldSegment;
use Neo\Models\Property;
use Neo\Models\PropertyFieldSegmentValue;

class UpdateDemographicFieldsJob implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $delay = 60;

    public function uniqueId() {
        $pId = $this->propertyId ? (string)$this->propertyId : 'all';
        $fId = $this->fieldId ? (string)$this->fieldId : 'all';

        return "$pId-$fId";
    }

    public function __construct(protected int|null $propertyId = null,
                                protected int|null $fieldId = null) {
    }

    public function handle() {
        // Make sure we have either a fieldId or a propertyId
        if (!$this->propertyId && !$this->fieldId) {
            return;
        }

        $fields = Field::query()->when($this->fieldId, function (Builder $query) {
            $query->where("id", "=", $this->fieldId);
        }, function (Builder $query) {
            $property = Property::query()->find($this->propertyId);
            $query->whereIn("id", DB::query()->from("fields_networks")
                                    ->where("network_id", "=", $property->network_id)
                                    ->distinct()
                                    ->get("field_id")
                                    ->pluck("field_id"));
            $query->where("demographic_filled", "=", true);
        })->with("segments")->get();

        $propertyIds = $this->propertyId
            ? collect($this->propertyId)
            : Property::query()->whereIn("network_id", DB::query()->from("fields_networks")
                                                         ->whereIn("field_id", $fields->pluck("id"))
                                                         ->distinct()
                                                         ->get("network_id")
                                                         ->pluck("network_id"))->get("actor_id")->pluck("actor_id");

        // List all the demo variables
        $demoVariables = $fields->flatMap(fn(Field $field) => $field->segments->pluck("variable_id"))->filter()->unique();
        $demoValues    = DemographicValue::query()
                                         ->whereIn("value_id", $demoVariables)
                                         ->whereIn("property_id", $propertyIds)
                                         ->get();

        // Now loop over each fields and segments, for each property, and fill in the values;
        /** @var Field $field */
        foreach ($fields as $field) {
            /** @var FieldSegment $segment */
            foreach ($field->segments as $segment) {
                $segmentValues = $demoValues->where("value_id", "=", $segment->variable_id);


                foreach ($propertyIds as $propertyId) {
                    /** @var DemographicValue|null $demoValue */
                    $demoValue = $segmentValues->firstWhere("property_id", "=", $propertyId);

                    if (!$demoValue) {
                        continue;
                    }

                    /** @var PropertyFieldSegmentValue $entry */
                    $entry = PropertyFieldSegmentValue::query()->firstOrNew([
                        "property_id"       => $propertyId,
                        "fields_segment_id" => $segment->getKey()
                    ]);
                    // We go the pedantic way here because `value` is a generic word and may conflict with Eloquent methods.
                    $entry->setAttribute("value", $demoValue->value);
                    $entry->setAttribute("reference_value", $demoValue->reference_value);
                    $entry->save();
                }
            }
        }
    }
}
