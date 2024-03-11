<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListSetsRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\IndexSets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Modules\Demographics\Models\IndexSetTemplate;
use Neo\Modules\Properties\Models\Property;
use Neo\Rules\PublicRelations;

class ListSetsRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["sometimes", new Exists(Property::class, "actor_id")],
            "template_id" => ["sometimes", new Exists(IndexSetTemplate::class, "id")],

            "page"  => ["integer"],
            "count" => ["integer"],

            "with" => ["array", new PublicRelations(IndexSetTemplate::class)],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
