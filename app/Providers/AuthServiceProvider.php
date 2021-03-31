<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AuthServiceProvider.php
 */

namespace Neo\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Auth\AccessTokenGuard;
use Neo\Auth\FirstLoAGuard;
use Neo\Auth\FourthLoAGuard;
use Neo\Auth\SecondLoAGuard;
use Neo\Auth\ThirdLoAGuard;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Models\Traits\HasCapabilities;

class AuthServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register convenient FFMpeg initializer
        $this->app->bind(UserProvider::class, Auth::createUserProvider());
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void {
        // Register our JWT Authentication providers
        Auth::extend('neo-loa-4', fn($app, $name, array $config) =>
            new FourthLoAGuard(Auth::createUserProvider($config['provider'])));

        Auth::extend('neo-loa-3', fn($app, $name, array $config) =>
            new ThirdLoAGuard(Auth::createUserProvider($config['provider'])));

        Auth::extend('neo-loa-2', fn($app, $name, array $config) =>
            new SecondLoAGuard(Auth::createUserProvider($config['provider'])));

        Auth::extend('neo-loa-1', fn($app, $name, array $config) =>
            new FirstLoAGuard(Auth::createUserProvider($config['provider'])));


        // Register the AccessToken Authentication provider
        Auth::extend("access-tokens", fn($app, $name, array $config) =>
            new AccessTokenGuard());


        // Register our gate authorization provider
        Gate::before(
            fn($model, string $capability) => $model->hasCapability(Capability::coerce($capability))
        );
    }
}
