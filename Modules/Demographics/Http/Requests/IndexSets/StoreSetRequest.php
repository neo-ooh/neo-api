<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreSetRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\IndexSets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Modules\Demographics\Models\IndexSetTemplate;
use Neo\Modules\Properties\Models\Property;
use Neo\Rules\PublicRelations;

class StoreSetRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["required", new Exists(Property::class, "actor_id")],
            "template_id" => ["required", new Exists(IndexSetTemplate::class, "id")],

            "primary_extract_id" => ["required", "integer", new Exists(Extract::class, "id")],
            "reference_extract_id" => ["required", "integer", new Exists(Extract::class, "id")],

            "with" => ["array", new PublicRelations(IndexSetTemplate::class)],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
