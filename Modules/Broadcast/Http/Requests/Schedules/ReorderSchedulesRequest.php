<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReorderSchedulesRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Rules\PublicRelations;

class ReorderSchedulesRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::contents_schedule->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "schedule_id" => ["required", "integer"],
            "order"       => ["required", "integer"],

            "with" => ["array", new PublicRelations(Schedule::class)]
        ];
    }
}
