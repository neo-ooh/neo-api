<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateAttachmentRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Attachments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateAttachmentRequest extends FormRequest {
    public function rules(): array {
        return [
            "locale" => ["required", "string"],
            "name"   => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::products_attachments_edit->value);
    }
}
