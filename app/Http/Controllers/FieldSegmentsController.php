<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldSegmentsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Fields\DestroyFieldSegmentRequest;
use Neo\Http\Requests\Fields\StoreFieldSegmentRequest;
use Neo\Http\Requests\Fields\UpdateFieldSegmentRequest;
use Neo\Models\Field;
use Neo\Models\FieldSegment;

class FieldSegmentsController {
    public function store(StoreFieldSegmentRequest $request, Field $field) {
        $segment = new FieldSegment([
            "name_en" => $request->input("name_en"),
            "name_fr" => $request->input("name_fr"),
            "order"   => $field->segments()->count(),
            "color"   => $request->input("color"),
        ]);

        if ($field->demographic_filled) {
            $segment->variable_id = $request->input("variable_id");
        }

        $field->segments()->save($segment);

        return new Response($segment, 201);
    }

    public function update(UpdateFieldSegmentRequest $request, Field $field, FieldSegment $segment) {
        $segment->name_en     = $request->input("name_en");
        $segment->name_fr     = $request->input("name_fr");
        $segment->order       = $request->input("order");
        $segment->color       = $request->input("color");
        $segment->variable_id = $field->demographic_filled ? $request->input("variable_id") : null;

        $segment->save();

        return new Response($segment);
    }

    public function destroy(DestroyFieldSegmentRequest $request, Field $field, FieldSegment $segment) {
        $segment->delete();

        return new Response($segment);
    }
}
