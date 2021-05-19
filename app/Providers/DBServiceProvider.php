<?php

namespace Neo\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Neo\Models\BroadcasterConnection;
use Neo\Models\ConnectionSettingsBroadSign;
use Neo\Models\ConnectionSettingsPiSignage;
use Neo\Models\Network;
use Neo\Models\NetworkSettingsBroadSign;
use Neo\Models\NetworkSettingsPiSignage;
use Neo\Services\Broadcast\Broadcaster;
use function Psy\debug;

class DBServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
