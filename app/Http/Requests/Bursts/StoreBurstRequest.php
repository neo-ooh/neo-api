<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreBurstRequest.php
 */

namespace Neo\Http\Requests\Bursts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreBurstRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows("bursts.request");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "report_id"    => ["required", "integer", "exists:reports,id"],
            "start_at"     => ["required", "date"],
            "scale_factor" => ["required", "integer", "min:1", "max:100"],
            "duration_ms"  => ["required", "integer"],
            "frequency_ms" => ["required", "integer"],
            "locations"    => ["required", "array"],
            "locations.*"  => ["integer", "exists:locations,id"]
        ];
    }
}
