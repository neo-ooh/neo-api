<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListSchedulesRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Rules\PublicRelations;

class ListSchedulesRequest extends FormRequest {
	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(Schedule::class)],
		];
	}

	public function authorize(): bool {
		return Gate::allows(Capability::campaigns_view->value);
	}
}
