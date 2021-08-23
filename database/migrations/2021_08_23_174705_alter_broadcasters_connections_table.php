<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\BroadcasterConnection;
use Neo\Models\Casts\ConnectionSettingsBroadSign;
use Neo\Models\Casts\ConnectionSettingsPiSignage;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("broadcasters_connections", function (Blueprint $table)
        {
            $table->set("broadcaster", ["broadsign", "pisignage", "odoo"])->change();
            $table->json("settings")->after("active");

        });

        $broadsignSettings = \Illuminate\Support\Facades\DB::table("connection_settings_broadsign")->get();

        foreach ($broadsignSettings as $settings) {
            $broadcaster = BroadcasterConnection::find($settings->connection_id);
            $broadcaster->settings = new ConnectionSettingsBroadSign((array)$settings);
            $broadcaster->save();
        }

        $pisignageSettings = \Illuminate\Support\Facades\DB::table("connection_settings_pisignage")->get();

        foreach ($pisignageSettings as $settings) {
            $broadcaster = BroadcasterConnection::find($settings->connection_id);
            $broadcaster->settings = new ConnectionSettingsPiSignage((array)$settings);
            $broadcaster->save();
        }

        Schema::drop("connection_settings_broadsign");
        Schema::drop("connection_settings_pisignage");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
