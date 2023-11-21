<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FieldSegmentsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\Fields\DestroyFieldSegmentRequest;
use Neo\Modules\Properties\Http\Requests\Fields\StoreFieldSegmentRequest;
use Neo\Modules\Properties\Http\Requests\Fields\UpdateFieldSegmentRequest;
use Neo\Modules\Properties\Jobs\Properties\UpdateDemographicFieldsJob;
use Neo\Modules\Properties\Models\Field;
use Neo\Modules\Properties\Models\FieldSegment;

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

		UpdateDemographicFieldsJob::dispatch(null, $field->getKey());

		return new Response($segment, 201);
	}

	public function update(UpdateFieldSegmentRequest $request, Field $field, FieldSegment $segment) {
		$segment->name_en     = $request->input("name_en");
		$segment->name_fr     = $request->input("name_fr");
		$segment->order       = $request->input("order");
		$segment->color       = $request->input("color");
		$segment->variable_id = $field->demographic_filled ? $request->input("variable_id") : null;

		$updateValues = $segment->isDirty("variable_id");

		$segment->save();

		if ($updateValues) {
			UpdateDemographicFieldsJob::dispatch(null, $field->getKey());
		}

		return new Response($segment);
	}

	public function destroy(DestroyFieldSegmentRequest $request, Field $field, FieldSegment $segment) {
		$segment->delete();

		return new Response($segment);
	}
}
