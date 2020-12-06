<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreActorRequest.php
 */

namespace Neo\Http\Requests\Actors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreActorRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        return Gate::allows(Capability::actors_create);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "is_group"       => [ "required", "boolean" ],
            "name"           => [ "required", "string" ],
            "email"          => [ "required_unless:is_group,true", "email", "unique:actors,email" ],
            "parent_id"      => [ "required", "numeric", "exists:actors,id" ],
            "branding_id"    => [ "sometimes", "numeric", "nullable", "exists:brandings,id" ],
            "roles"          => [ "sometimes", "array", "distinct" ],
            "roles.*"        => [ "integer", "exists:roles,id" ],
            "capabilities"   => [ "sometimes", "array", "distinct" ],
            "capabilities.*" => [ "integer", "exists:capabilities,id" ],
        ];
    }
}
