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
use Neo\Models\AccessToken;
use Neo\Models\Actor;

class AuthServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void {
        $this->app->bind(UserProvider::class, Auth::createUserProvider());
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void {
        // Register our JWT Authentication providers
        Auth::extend('neo-loa-4',
            static fn($app, $name, array $config) => new FourthLoAGuard(Auth::createUserProvider($config['provider']))
        );

        Auth::extend('neo-loa-3',
            static fn($app, $name, array $config) => new ThirdLoAGuard(Auth::createUserProvider($config['provider']))
        );

        Auth::extend('neo-loa-2',
            static fn($app, $name, array $config) => new SecondLoAGuard(Auth::createUserProvider($config['provider'])));


        Auth::extend('neo-loa-1',
            static fn($app, $name, array $config) => new FirstLoAGuard(Auth::createUserProvider($config['provider']))
        );


        // Register the AccessToken Authentication provider
        Auth::extend("access-tokens",
            static fn($app, $name, array $config) => new AccessTokenGuard()
        );


        // Register our gate authorization provider
        Gate::before(
            static function (Actor|AccessToken $model, string $capability) {
                return $model->hasCapability(Capability::tryFrom($capability));
            }
        );
    }
}
