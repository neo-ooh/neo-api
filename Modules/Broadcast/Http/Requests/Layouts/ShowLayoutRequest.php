<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowLayoutRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Layouts;

use Illuminate\Foundation\Http\FormRequest;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Rules\PublicRelations;

class ShowLayoutRequest extends FormRequest {
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
            "with" => ["array", new PublicRelations(Layout::class)],
        ];
    }
}
