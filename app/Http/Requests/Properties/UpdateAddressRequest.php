<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateAddressRequest.php
 */

namespace Neo\Http\Requests\Properties;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class UpdateAddressRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::properties_edit);
    }

    public function rules() {
        return [
            "line_1" => ["required", "string"],
            "line_2" => ["string"],
            "province" => ["required", "string", "exists:provinces,slug"],
            "city" => ["required", "string"],
            "zipcode" => ["required", "string"],
        ];
    }
}
