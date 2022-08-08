<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreMarketRequest.php
 */

namespace Neo\Http\Requests\Markets;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StoreMarketRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::properties_markets->value);
    }

    public function rules() {
        return [
            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],
        ];
    }
}
