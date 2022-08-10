<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncActorRolesRequest.php
 */

namespace Neo\Http\Requests\ActorsRoles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SyncActorRolesRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        // User needs to be connected , have the `edit_user` capability and has access to the referenced user
        $gate   = Gate::allows(Capability::actors_edit->value);
        $access = Auth::user()->hasAccessTo($this->route('actor'));
        return $gate && $access;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "roles"   => ["nullable", "array"],
            "roles.*" => ["integer", "exists:roles,id", "distinct"],
        ];
    }
}
