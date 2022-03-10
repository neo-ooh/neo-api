<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RecycleTwoFARequest.php
 */

namespace Neo\Http\Requests\Actors;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Neo\Enums\Capability;
use Neo\Models\Actor;

class RecycleTwoFARequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        /** @var Actor $actor */
        $actor = $this->route('actor');

        if (!Auth::user()->is_group && Auth::user()->is($actor)) {
            return true; // The actor can update itself
        }

        // Otherwise, the `actors_auth` capability is required
        return Gate::allows(Capability::actors_auth);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            //
        ];
    }
}
