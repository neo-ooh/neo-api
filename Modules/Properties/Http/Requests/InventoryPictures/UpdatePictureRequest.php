<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdatePictureRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryPictures;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Enums\InventoryPictureType;
use Neo\Modules\Properties\Rules\AccessibleProduct;

class UpdatePictureRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"        => ["nullable", "string"],
            "description" => ["nullable", "string"],

            "type"       => ["required", new Enum(InventoryPictureType::class)],
            "product_id" => ["sometimes", new AccessibleProduct(allowNull: true)],

            "order" => ["numeric"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_pictures_edit->value);
    }
}
