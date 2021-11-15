<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateActorRequest.php
 */

namespace Neo\Http\Requests\Actors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;
use Neo\Models\Actor;

class UpdateActorRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     * A User can update a user only if it is itself, or if the user is a child, and the user has the proper capability
     *
     * @return bool
     */
    public function authorize(): bool {
        // This is the ID of the actor targeted by the route
        /** @var Actor $actor */
        $actor = $this->route('actor');

        if (!Auth::user()->is_group && Auth::user()->is($actor)) {
            return true; // The actor can update itself
        }

        if (!Gate::allows(Capability::actors_edit)) {
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
            "name"           => ["sometimes", "string"],
            "email"          => ["sometimes", "exclude_unless:is_group,false", "email", Rule::unique('actors')
                                                                                            ->ignore($this->route('actor')->id)],
            "locale"         => ["sometimes", "string"],
            "password"       => ["sometimes", "string", "min:6"],
            "is_locked"      => ["sometimes", "boolean"],
            "parent_id"      => ["sometimes", "integer", "exists:actors,id"],
            "branding_id"    => ["sometimes", "present"],
            "limited_access" => ["sometimes", "boolean"],
            "two_fa_method"  => ["sometimes", "string"]
        ];
    }
}
