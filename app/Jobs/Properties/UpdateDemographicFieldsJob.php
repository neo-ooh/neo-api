<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateDemographicFieldsJob.php
 */

namespace Neo\Jobs\Properties;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Properties\Models\DemographicValue;
use Neo\Modules\Properties\Models\Field;
use Neo\Modules\Properties\Models\FieldSegment;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyFieldSegmentValue;

class UpdateDemographicFieldsJob implements ShouldQueue, ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId() {
        $pId = $this->propertyId ? (string)$this->propertyId : 'all';
        $fId = $this->fieldId ? (string)$this->fieldId : 'all';

        return "$pId-$fId";
    }

    public function __construct(
        protected int|null $propertyId = null,
        protected int|null $fieldId = null
    ) {
        $this->delay = 30;
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
                                    ->pluck("field_id"));
            $query->where("demographic_filled", "=", true);
        })->with("segments")->get();


        $segmentsIds = $fields->flatMap(fn(Field $field) => $field->segments->pluck("id"));

        $propertyIds = $this->propertyId
            ? collect($this->propertyId)
            : Property::query()->whereIn("network_id", DB::query()->from("fields_networks")
                                                         ->whereIn("field_id", $fields->pluck("id"))
                                                         ->distinct()
                                                         ->pluck("network_id"))->get("actor_id")->pluck("actor_id");

        // List all the demo variables
        $demoVariables = $fields->flatMap(fn(Field $field) => $field->segments->pluck("variable_id"))->filter()->unique();
        $demoValues    = DemographicValue::query()
                                         ->whereIn("value_id", $demoVariables)
                                         ->whereIn("property_id", $propertyIds)
                                         ->get();

        $propertiesValues = PropertyFieldSegmentValue::query()
                                                     ->whereIn("property_id", $propertyIds)
                                                     ->whereIn("fields_segment_id", $segmentsIds)
                                                     ->get();

        // Now loop over each fields and segments, for each property, and fill in the values;
        /** @var Field $field */
        foreach ($fields as $field) {
//            dump($field->name_en);

            /** @var FieldSegment $segment */
            foreach ($field->segments as $segment) {
//                dump("-- $segment->variable_id");
                $segmentValues = $demoValues->where("value_id", "=", $segment->variable_id);
//                dump($segmentValues->count());

                foreach ($propertyIds as $propertyId) {
                    /** @var DemographicValue|null $demoValue */
                    $demoValue = $segmentValues->firstWhere("property_id", "=", $propertyId);

//                    dump("-- -- $propertyId : " . ($demoValue?->value ?? "missing"));

                    if (!$demoValue) {
                        continue;
                    }


                    /** @var PropertyFieldSegmentValue $entry */
                    $entry = $propertiesValues->first(
                        fn(PropertyFieldSegmentValue $segmentValue) => $segmentValue->property_id === $propertyId && $segmentValue->fields_segment_id === $segment->getKey(),
                        default: fn() => new PropertyFieldSegmentValue([
                                                                           "property_id"       => $propertyId,
                                                                           "fields_segment_id" => $segment->getKey(),
                                                                       ]));

                    // We go the pedantic way here because `value` is a generic word and may conflict with Eloquent methods.
                    $entry->setAttribute("value", $demoValue->value);
                    $entry->setAttribute("reference_value", $demoValue->reference_value);

//                    dump($entry->toArray(), $entry->isDirty());
                    $entry->save();
                }
            }
        }
    }
}
