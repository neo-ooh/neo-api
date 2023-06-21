<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPicturesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryPictures;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Rules\AccessibleProduct;
use Neo\Modules\Properties\Rules\AccessibleProperty;

class ListPicturesRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["required_without:product_id", new AccessibleProperty()],
            "product_id"  => ["required_without:property_id", new AccessibleProduct()],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_pictures_view->value);
    }
}
