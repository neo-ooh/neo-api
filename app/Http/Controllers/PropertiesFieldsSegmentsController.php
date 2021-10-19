<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesFieldsSegmentsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Properties\DestroyFieldSegmentValueRequest;
use Neo\Http\Requests\Properties\StoreFieldSegmentValueRequest;
use Neo\Models\Field;
use Neo\Models\Property;
use Neo\Models\PropertyFieldSegmentValue;

class PropertiesFieldsSegmentsController {
    public function store(StoreFieldSegmentValueRequest $request, Property $property, Field $field) {
        $segmentId = $request->input("segment_id");
        $value     = $request->input("value");

        $entry = PropertyFieldSegmentValue::query()->firstOrNew([
            "property_id"      => $property->getKey(),
            "field_segment_id" => $segmentId
        ]);
        // We go the pedantic way here because `value` is a generic word and may conflict with Eloquent methods.
        $entry->setAttribute("value", $value);
        $entry->save();

        return new Response($entry);
    }

    public function destroy(DestroyFieldSegmentValueRequest $request, Property $property, Field $field) {
        PropertyFieldSegmentValue::query()
                                 ->where("property_id", "=", $property->getKey())
                                 ->where("field_segment_id", "=", $request->input("segment_id"))
                                 ->delete();

        return new Response();
    }
}