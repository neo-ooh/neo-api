<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyCreativeRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Creatives;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Creative;

class DestroyCreativeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        if (!Gate::allows(Capability::contents_edit->value)) {
            return false;
        }

        /** @noinspection NullPointerExceptionInspection */
        $creativeId = $this->route()->originalParameter("creative");

        /** @var Creative $creative */
        $creative = Creative::query()->findOrFail($creativeId);

        return $creative->content->library->isAccessibleBy(Auth::user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            //
        ];
    }
}
