<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateCampaignRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Broadcast\Models\BroadcastTag;
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
			"start_date" => ["required", "date_format:Y-m-d"],
			"start_time" => ["required", "date_format:H:i:s"],
			"end_date"   => ["required", "date_format:Y-m-d"],
			"end_time"   => ["required", "date_format:H:i:s"],

			"broadcast_days" => ["required", "integer", "max:127"],

			"flight_id" => ["nullable", new Exists(ContractFlight::class, "id")],

			"occurrences_in_loop"            => ["required", "numeric", "min:0"],
			"priority"                       => ["required", "integer", "min:0"],
			"static_duration_override"       => ["required", "numeric", "min:0"],
			"dynamic_duration_override"      => ["required", "numeric", "min:0"],
			"default_schedule_duration_days" => ["required", "numeric", "min:0"],

			"tags"   => ["array"],
			"tags.*" => ["int", new Exists(BroadcastTag::class, "id")],

			"with" => ["array", new PublicRelations(Campaign::class)],
		];
	}
}
