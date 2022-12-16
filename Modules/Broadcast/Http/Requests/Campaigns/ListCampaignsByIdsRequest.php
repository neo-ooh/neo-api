<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListCampaignsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Rules\AccessibleCampaign;
use Neo\Rules\PublicRelations;

class ListCampaignsByIdsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::campaigns_edit->value)
            || Gate::allows(Capability::contents_schedule->value)
            || Gate::allows(Capability::contents_review->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "ids"   => ["array"],
            "ids.*" => ["integer", new AccessibleCampaign()],
            "with"  => ["array", new PublicRelations(Campaign::class)],
        ];
    }
}
