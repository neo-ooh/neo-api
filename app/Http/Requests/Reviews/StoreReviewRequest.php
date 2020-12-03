<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Http\Requests\Reviews;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreReviewRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        $gate = Gate::allows(Capability::contents_review);
        $access = Auth::user()->canAccessCampaign($this->route("schedule")->campaign);
        return $gate && $access;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "approved" => [ "required", "boolean" ],
            "message"  => [ "nullable", "string" ],
        ];
    }
}
