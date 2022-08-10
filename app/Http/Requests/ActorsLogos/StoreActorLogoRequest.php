<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreActorLogoRequest.php
 */

namespace Neo\Http\Requests\ActorsLogos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Actor;

class StoreActorLogoRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     * A User can update a user only if the user is a child, and the user has the proper capability. A user cannot update its own
     * logo
     *
     * @return bool
     */
    public function authorize(): bool {
        // This is the ID of the actor targeted by the route
        /** @var Actor $actor */
        $actor = $this->route('actor');

        if (!Auth::user()->is_group && Auth::user()->is($actor)) {
            return true; // The actor can update its own logo
        }

        if (!Gate::allows(Capability::actors_edit->value)) {
            return false; // The actor doesn't have the proper capability
        }

        // Check if the actor we want to update is a descendant of the current one
        return Auth::user()->hasAccessTo($actor);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "file" => ["required", "image"],
        ];
    }
}
