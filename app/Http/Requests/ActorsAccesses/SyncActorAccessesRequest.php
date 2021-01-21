<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateActorRequest.php
 */

namespace Neo\Http\Requests\ActorsAccesses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Actor;

class SyncActorAccessesRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     * A User can update a user only if it is itself, or if the user is a child, and the user has the proper capability
     *
     * @return bool
     */
    public function authorize (): bool {
        // Current user cannot edit its own access
        // Current user must have the actors.edit capabilities
        // Current user must have access to the actor it is editing (Checked through controller's method's binding)

        /** @var Actor $actor */
        $actor = $this->route('actor');

        return !$actor->is(Auth::user()) && Gate::allows(Capability::actors_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "actors" => ["required", "array"],
            "actors.*" => ["integer", "exists:actors,id"]
        ];
    }
}
