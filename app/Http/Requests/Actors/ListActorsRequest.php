<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
use Neo\Models\Actor;
use Neo\Rules\AccessibleActor;
use Neo\Rules\PublicRelations;

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

            "parent_id" => ["sometimes", "integer", new AccessibleActor(true)],

            "capabilities"   => ["array"],
            "capabilities.*" => [new Enum(Capability::class)],

            // Legacy
            "exclude"        => ["sometimes", "array"],
            "exclude.*"      => ["integer", "exists:actors,id"],
            "groups"         => ["sometimes", "boolean"],

            "with" => ["array", new PublicRelations(Actor::class)],
        ];
    }
}
