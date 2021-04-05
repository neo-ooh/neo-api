<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeatherRecordsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'weather_records';

    /**
     * Run the migrations.
     * @table weather_records
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('endpoint', 3);
            $table->foreignId("location_id")->constrained("weather_locations");
            $table->string('locale', 5);
            $table->text('content');

            $table->unique(["endpoint", "location_id", "locale"], 'records_endpoint_location_id_locale_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}
