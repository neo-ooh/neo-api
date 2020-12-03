<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Rules\AccessibleContent;

class InsertScheduleRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        $allowed = Gate::allows(Capability::contents_schedule);
        $campaign = Auth::user()->canAccessCampaign($this->route("campaign"));
        return $allowed && $campaign;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "content_id" => [ "required", "integer", new AccessibleContent ],
            "order"      => [ "required", "integer" ],
        ];
    }
}
