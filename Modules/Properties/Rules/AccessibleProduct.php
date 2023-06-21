<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleProduct.php
 */

namespace Neo\Modules\Properties\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Neo\Models\AccessToken;
use Neo\Models\Actor;
use Neo\Modules\Properties\Models\Product;

class AccessibleProduct implements Rule, ImplicitRule {
    /**
     * Create a new rule instance.
     */
    public function __construct(protected bool $allowNull = false) {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool {
        if (is_null($value) && $this->allowNull) {
            return true;
        }

        // Load the product and see if it exists
        /** @var Actor|null $product */
        $product = Product::query()->find($value)?->load("property");

        if (is_null($product)) {
            return false;
        }

        /** @var AccessToken|Actor $user */
        $user = Auth::user();

        if ($user instanceof AccessToken) {
            return true;
        }

        // Finally, check if the current user can access the product's property
        return $user->getAccessibleActors(ids: true)->contains($product->property->getKey());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string {
        return 'You do not have access to the specified product';
    }
}
