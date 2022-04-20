<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchPropertiesRequest.php
 */

namespace Neo\Http\Requests\Properties;

use Illuminate\Foundation\Http\FormRequest;

class SearchPropertiesRequest extends FormRequest {
    public function rules() {
        return [
            "q" => ["required", "string"]
        ];
    }

    public function authorize() {
//        return Gate::allows(Capability::properties_edit);
        return true;
    }
}
