<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateReviewTemplateRequest.php
 */

namespace Neo\Http\Requests\ReviewsTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Rules\AccessibleActor;

class UpdateReviewTemplateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        return Gate::allows(Capability::contents_review) && (
                Auth::id() === $this->route("template")->owner_id ||
                Auth::user()->hasAccessTo($this->route("template")->owner)
            );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "text"     => ["required", "string"],
            "owner_id" => ["required", "integer", new AccessibleActor()],
        ];
    }
}
