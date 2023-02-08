<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateContactRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PropertiesContacts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateContactRequest extends FormRequest {
    public function rules(): array {
        return [
            "role" => ["string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_contacts_edit->value);
    }
}
