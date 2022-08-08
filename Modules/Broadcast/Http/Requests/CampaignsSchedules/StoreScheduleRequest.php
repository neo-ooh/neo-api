<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreScheduleRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Rules\AccessibleContent;
use Neo\Rules\PublicRelations;

class StoreScheduleRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::contents_schedule->value);
    }

    public function rules(): array {
        return [
            "content_id" => ["required", "int", new AccessibleContent()],
            "order"      => ["required", "int"],

            "with" => ["array", new PublicRelations(Schedule::class)],
        ];
    }
}
