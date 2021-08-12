<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateCityRequest.php
 */

namespace Neo\Http\Requests\Cities;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class UpdateCityRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::properties_markets);
    }

    public function rules() {
        return [
            "name" => ["required", "string"],
            "market_id" => ["integer", "exists:markets,id"]
        ];
    }
}