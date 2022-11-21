<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 */

namespace Neo\Http\Requests\Advertisers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Advertiser;
use Neo\Rules\PublicRelations;

class ListAdvertisersRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(Advertiser::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::advertiser_edit->value);
    }
}
