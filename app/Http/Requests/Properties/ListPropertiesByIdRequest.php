<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPropertiesRequest.php
 */

namespace Neo\Http\Requests\Properties;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Models\Property;
use Neo\Rules\PublicRelations;

class ListPropertiesByIdRequest extends FormRequest {
    public function rules() {
        return [
            "ids"  => ["required", "array"],
            "with" => ["array", new PublicRelations(Property::class)],
        ];
    }

    public function authorize() {
        return Gate::allows(Capability::tools_planning->value);
    }
}
