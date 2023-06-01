<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPropertyTypesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PropertyTypes;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\PropertyType;
use Neo\Rules\PublicRelations;

class ListPropertyTypesRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => [new PublicRelations(PropertyType::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_types_edit->value)
            || Gate::allows(Capability::properties_view->value);
    }
}
