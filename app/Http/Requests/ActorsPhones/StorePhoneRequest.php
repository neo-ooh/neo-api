<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyPhoneRequest.php
 */

namespace Neo\Http\Requests\ActorsPhones;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Actor;

class StorePhoneRequest extends FormRequest {
    public function rules(): array {
        return [
            "phone"         => ["sometimes", "phone"],
            "phone_country" => ["required_with:phone"],
        ];
    }

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
}
