<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdatePropertyDataRequest.php
 */

namespace Neo\Http\Requests\PropertiesData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdatePropertyDataRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::properties_edit);
    }

    public function rules() {
        return [
            "website" => ["nullable", "url"],
            "description_fr" => ["nullable", "string"],
            "description_en" => ["nullable", "string"],
            "stores_count"   => ["nullable", "integer"],
            "visit_length"   => ["nullable", "numeric"],
            "average_income" => ["nullable", "numeric"],
            "is_downtown"    => ["nullable", "boolean"],
            "data_source"    => ["nullable", "string"],
            "market_population" => ["nullable", "integer"],
            "gross_area" => ["nullable", "integer"],
            "spending_per_visit" => ["nullable", "integer"],
        ];
    }
}
