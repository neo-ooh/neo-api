<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreCampaignRequest.php
 */

namespace Neo\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Rules\AccessibleActor;

class StoreCampaignRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::campaigns_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "owner_id"         => ["required", "integer", new AccessibleActor()],
            "network_id"       => ["required", "integer", "exists:networks,id"],
            "format_id"        => ["required", "integer", "exists:formats,id"],
            "name"             => ["nullable", "string"],
            "display_duration" => ["required", "numeric", "min:1"],
            "start_date"       => ["required", "date"],
            "end_date"         => ["required", "date"],
            "loop_saturation"  => ["required", "integer", "min:0"],
            "priority"         => ["required", "integer", "min:0"],
        ];
    }
}
