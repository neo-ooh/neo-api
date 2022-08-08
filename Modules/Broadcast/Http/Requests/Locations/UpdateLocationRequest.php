<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateLocationRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Rules\PublicRelations;

class UpdateLocationRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows('locations.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "name" => ["required", "string"],

            "scheduled_sleep" => ["sometimes", "boolean"],
            "sleep_end"       => ["nullable", "required_if:scheduled_sleep,true", "date_format:H:i"],
            "sleep_start"     => ["nullable", "required_if:scheduled_sleep,true", "date_format:H:i"],

            "with" => ["sometimes", "array", new PublicRelations(Location::class)],
        ];
    }
}
