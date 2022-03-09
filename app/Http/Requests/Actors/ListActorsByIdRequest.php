<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListActorsByIdRequest.php
 */

namespace Neo\Http\Requests\Actors;

use Illuminate\Foundation\Http\FormRequest;

class ListActorsByIdRequest extends FormRequest {
    public function rules(): array {
        return [
            "ids" => ["required", "array"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
