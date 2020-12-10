<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - SecuredModel.php
 */

namespace Neo\Models;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

abstract class SecuredModel extends Model {
    use Traits\AccessValidation;

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule;

    public function resolveRouteBinding ($value, $field = null): ?Model {
        // Validate user access to this model
        $accessible = $this->validateAccess($value);

        // Can it access it ?
        if (!$accessible) {
            $this->onUnauthorized($value);
        }

        // Yes
        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Perform late access checking.
     *
     * This method is useful when you want to validate that the current user can access this resource, but cannot handle this using RouteModelBinding
     * @throws AuthorizationException
     * @throws Exception
     */
    public function authorizeAccess(): bool
    {
        if (!$this->validateAccess($this->getKey())) {
            $this->onUnauthorized();
        }

        return true;
    }

    /**
     * @param null $key
     * @throws AuthorizationException
     */
    private function onUnauthorized($key = null) {
        if(!$key) {
            $key = $this->getKey();
        }

        throw new AuthorizationException("Your are not allowed to access this resource: " . static::class . "#{$key}.", 403);
    }
}
