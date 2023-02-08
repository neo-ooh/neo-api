<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreTrafficRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PropertiesTraffic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreTrafficRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::properties_traffic_fill->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "year"      => ["required", "integer"],
            "month"     => ["required", "numeric"],
            "traffic"   => ["sometimes", "nullable", "numeric"],
            "temporary" => ["sometimes", "nullable", "numeric"],
        ];
    }
}
