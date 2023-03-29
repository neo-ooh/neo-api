<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreMarketRequest.php
 */

namespace Neo\Http\Requests\Markets;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\Province;

class StoreMarketRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::properties_markets->value);
    }

    public function rules() {
        return [
            "province_id" => ["required", new Exists(Province::class, "id")],

            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],

            "area" => ["nullable", "array"],
        ];
    }
}
