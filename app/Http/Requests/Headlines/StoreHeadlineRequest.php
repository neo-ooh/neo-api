<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreHeadlineRequest.php
 */

namespace Neo\Http\Requests\Headlines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreHeadlineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::headlines_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "style" => ["required", "string"],
            "end_date" => ["required", "nullable", "date"],
            "messages" => ["required", "array"],
            "messages.*.locale" => ["required", "string"],
            "messages.*.message" => ["required", "string"],
            "capabilities" => ["sometimes", "array"],
            "capabilities.*" => ["integer", "exists:capabilities,id"],
        ];
    }
}
