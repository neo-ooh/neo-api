<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Rules\AccessibleActor;

class UpdateCampaignRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        return Gate::allows(Capability::campaigns_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "owner_id"         => [ "required", "integer", new AccessibleActor() ],
            "name"             => [ "required", "string" ],
            "display_duration" => [ "required", "numeric", "min:1" ],
            "content_limit"    => [ "required", "numeric", "min:0" ],
            "start_date"       => [ "required", "date" ],
            "end_date"         => [ "required", "date" ],
        ];
    }
}
