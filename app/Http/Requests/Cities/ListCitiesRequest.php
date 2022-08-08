<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListCitiesRequest.php
 */

namespace Neo\Http\Requests\Cities;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class ListCitiesRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::properties_edit->value);
    }

    public function rules() {
        return [
            //
        ];
    }
}
