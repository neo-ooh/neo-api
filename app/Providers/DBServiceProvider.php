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
        Relation::morphMap([
            'broadsign' => ConnectionSettingsBroadSign::class,
            'pisignage' => ConnectionSettingsPiSignage::class,
        ]);
        // Define dynamic relationships required by Eloquent
//        BroadcasterConnection::resolveRelationUsing("settings", function (BroadcasterConnection $connection) {
//            dump($connection);
//            switch ($connection->broadcaster) {
//                case Broadcaster::BROADSIGN:
//                    dump("broadsign");
//                    return $connection->hasOne(ConnectionSettingsBroadSign::class, "connection_id");
//                case Broadcaster::PISIGNAGE:
//                    dump("pisignage");
//                    return $connection->hasOne(ConnectionSettingsPiSignage::class, "connection_id");
//                default:
//                    return null;
//            }
//        });

        Network::resolveRelationUsing("settings", function (Network $network) {
            switch ($network->broadcaster_connection->broadcaster) {
                case Broadcaster::BROADSIGN:
                    return $network->hasOne(NetworkSettingsBroadSign::class, "connection_id");
                case Broadcaster::PISIGNAGE:
                    return $network->hasOne(NetworkSettingsPiSignage::class, "connection_id");
                default:
                    return null;
            }
        });
    }
}
