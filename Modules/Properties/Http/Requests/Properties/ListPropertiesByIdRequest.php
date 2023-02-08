<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPropertiesByIdRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Property;
use Neo\Rules\PublicRelations;

class ListPropertiesByIdRequest extends FormRequest {
    public function rules(): array {
        return [
            "ids"  => ["required", "array"],
            "with" => ["array", new PublicRelations(Property::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_view->value)
            || Gate::allows(Capability::planner_access->value);
    }
}
