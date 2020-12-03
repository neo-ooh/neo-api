<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Auth\JwtGuard;
use Neo\Auth\LightJwtGuard;
use Neo\Enums\Capability;
use Neo\Models\Actor;

class AuthServiceProvider extends ServiceProvider {

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot (): void {
        // Register our JWT Authentication providers
        Auth::extend('neo-jwt',
            fn ($app, $name, array $config) => new JwtGuard(Auth::createUserProvider($config['provider'])));
        Auth::extend('neo-jwt-light',
            fn ($app, $name, array $config) => new LightJwtGuard(Auth::createUserProvider($config['provider'])));

        // Register our gate authorization provider
        Gate::before(
            fn (Actor $actor, string $capability) => $actor->hasCapability(Capability::coerce($capability)));
    }
}
