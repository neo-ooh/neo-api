<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncCampaignLocationsRequest.php
 */

namespace Neo\Http\Requests\CampaignsLocations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SyncCampaignLocationsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        // User needs to be connected , have the `campaigns_edit` capability and has access to the referenced campaign
        return Gate::allows(Capability::campaigns_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "locations"   => ["nullable", "array"],
            "locations.*" => ["integer", "exists:locations,id", "distinct"],
        ];
    }
}
