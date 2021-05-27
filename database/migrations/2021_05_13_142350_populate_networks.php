<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Neo\Models\BroadcasterConnection;
use Neo\Models\ConnectionSettingsBroadSign;
use Neo\Models\Network;
use Neo\Models\NetworkSettingsBroadSign;
use function Ramsey\Uuid\v4;

return new class extends Migration {
    /**
     * Schema table name to migrate
     *
     * @var string
     */
    public string $tableName = "network_settings_pisignage";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // Before going further, we need to populate the newly-created connections and networks as they are needed by the next migrations

        // Create the broadsign connection
        $connection              = new BroadcasterConnection();
        $connection->uuid        = v4();
        $connection->name        = "BroadSign";
        $connection->broadcaster = "broadsign";
        $connection->save();

        // Set up the connection settings
        $settings                      = new ConnectionSettingsBroadSign();
        $settings->connection_id       = $connection->id;
        $settings->domain_id           = config("broadsign.domain-id");
        $settings->default_customer_id = config("broadsign.customer-id");
        $settings->default_tracking_id = config("broadsign.advertising-criteria");
        $settings->save();

        $connection->refresh();

        // Add the connection certificate
        Storage::put($connection->settings->certificate_path . $connection->settings->file_name, file_get_contents(storage_path("broadsign.pem")), ["visibility" => "private"]);

        // Set up the networks
        foreach (["Shopping"   => 392901509,
                  "Fitness"    => 392901503,
                  "On the Go"  => 392901515,
                  "MTL Office" => 434469029] as $key => $value) {
            // Shopping
            $network                = new Network();
            $network->uuid          = v4();
            $network->name          = $key;
            $network->connection_id = $connection->id;
            $network->save();

            $settings               = new NetworkSettingsBroadSign();
            $settings->network_id   = $network->id;
            $settings->container_id = $value;
            $settings->customer_id  = config("broadsign.customer-id");
            $settings->tracking_id  = config("broadsign.advertising-criteria");
            $settings->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists($this->tableName);
    }
};
