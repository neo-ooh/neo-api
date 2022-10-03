<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListActorsRequest.php
 */

namespace Neo\Http\Requests\Actors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\ActorType;
use Neo\Enums\Capability;

/**
 * Class ListActorsRequest
 *
 * @package Neo\Http\Requests
 */
class ListActorsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "withself" => ["sometimes", "boolean"],

            "types"   => ["array"],
            "types.*" => [new Enum(ActorType::class)],

            "capabilities"     => ["array"],
            "capabilities.*"   => [new Enum(Capability::class)],

            // Legacy
            "exclude"          => ["sometimes", "array"],
            "exclude.*"        => ["integer", "exists:actors,id"],
            "groups"           => ["sometimes", "boolean"],
            "details"          => ["sometimes", "boolean"],
            "campaigns_status" => ["sometimes", "boolean"],
            "property"         => ["sometimes", "boolean"],
            "capability"       => ["sometimes", "string", new Enum(Capability::class)],

            "with" => ["sometimes", "array"],
        ];
    }
}
