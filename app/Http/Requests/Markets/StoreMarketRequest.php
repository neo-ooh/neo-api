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
use GeoJson\Geometry\Polygon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\Province;
use Neo\Rules\GeoJsonRule;

class StoreMarketRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::properties_markets->value);
    }

    public function rules(): array {
        return [
            "province_id" => ["required", new Exists(Province::class, "id")],

            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],

            "area" => ["nullable", new GeoJsonRule(Polygon::class)],
        ];
    }
}
