<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateParamterRequest.php
 */

namespace Neo\Http\Requests\Parameters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\CommonParameters;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;

class UpdateParameterRequest extends FormRequest {
    public function rules(): array {
        $slug  = $this->route()?->originalParameter("parameter");
        $param = BroadcastParameters::tryFrom($slug) ?? CommonParameters::from($slug);

        return match ($param->format()) {
            "file:pdf" => [
                "value" => ["required", "file", "mimes:pdf"],
            ],
            "number"   => [
                "value" => ["required", "numeric"],
            ],
            default    => [
                "value" => ["required"],
            ]
        };
    }

    public function authorize(): bool {
        $slug  = $this->route()?->originalParameter("parameter");
        $param = BroadcastParameters::tryFrom($slug) ?? CommonParameters::from($slug);

        return Gate::allows($param->capability()->value);
    }
}
