<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListContractsRequest.php
 */

namespace Neo\Http\Requests\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ListContractsRequest extends FormRequest {
    public function rules(): array {
        return [
            "actor_id" => ["sometimes", "exists:actors,id"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows('contracts.edit');
    }
}
