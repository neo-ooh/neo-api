<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleInventoryResource.php
 */

namespace Neo\Modules\Properties\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;

class AccessibleInventoryResource implements Rule {
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool {
        /** @var InventoryResource $resource */
        $resource = InventoryResource::query()->findOrFail($value);

        // Depending on the type of the resource, we use the appropriate access rule
        return match ($resource->type) {
            InventoryResourceType::Product         =>
            (new AccessibleProduct())->passes("product_id", Product::firstWhere("inventory_resource_id", "=", $resource->getKey())
                                                                   ->getKey()),
            InventoryResourceType::Property        =>
            (new AccessibleProperty())->passes("property_id", Property::firstWhere("inventory_resource_id", "=", $resource->getKey())
                                                                      ->getKey()),
            InventoryResourceType::ProductCategory => true,
            InventoryResourceType::PropertyType    => Gate::allows(Capability::properties_types_edit->value),
        };
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string {
        return 'You do not have access to the specified inventory resource';
    }
}
