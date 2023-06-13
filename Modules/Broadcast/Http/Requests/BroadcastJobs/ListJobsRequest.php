<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListJobsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcastJobs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Enums\BroadcastJobStatus;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Rules\PublicRelations;

class ListJobsRequest extends FormRequest {
    public function rules(): array {
        return [
            "page"  => ["integer"],
            "count" => ["integer"],

            "status"        => ["sometimes", new Enum(BroadcastJobStatus::class)],
            "resource_type" => ["sometimes", new Enum(BroadcastResourceType::class)],

            "with" => ["array", new PublicRelations(BroadcastJob::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::dev_tools->value);
    }
}
