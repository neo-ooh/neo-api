<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListFieldsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Fields;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Field;
use Neo\Rules\PublicRelations;

class ListFieldsRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["sometimes", "array", new PublicRelations(Field::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::fields_edit->value);
    }
}
