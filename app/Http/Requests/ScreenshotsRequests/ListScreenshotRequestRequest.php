<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListScreenshotRequestRequest.php
 */

namespace Neo\Http\Requests\ScreenshotsRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\ContractFlight;
use Neo\Models\ScreenshotRequest;
use Neo\Rules\PublicRelations;

class ListScreenshotRequestRequest extends FormRequest {
    public function rules(): array {
        return [
            "flight_id" => ["required", "int", new Exists(ContractFlight::class, "id")],
            "with"      => ["sometimes", "array", new PublicRelations(ScreenshotRequest::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::screenshots_requests->value);
    }
}
