<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateCampaignRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Rules\AccessibleActor;
use Neo\Rules\PublicRelations;

class UpdateCampaignRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::campaigns_edit->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "parent_id"  => ["required", "integer", new AccessibleActor()],
            "name"       => ["required", "string"],
            "start_date" => ["required", "date:Y-m-d"],
            "start_time" => ["required", "date:H:m:s"],
            "end_date"   => ["required", "date:Y-m-d"],
            "end_time"   => ["required", "date:H:m:s"],

            "occurrences_in_loop" => ["required", "integer", "min:0"],
            "priority"            => ["required", "integer", "min:0"],

            "with" => ["array", new PublicRelations(Campaign::class)],
        ];
    }
}
