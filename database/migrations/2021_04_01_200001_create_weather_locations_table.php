<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeatherLocationsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'weather_locations';

    /**
     * Run the migrations.
     * @table weather_locations
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('country', 2);
            $table->string('province', 2);
            $table->string('city', 30);
            $table->string('selection', 10)->default('WEATHER');
            $table->integer('revert_date')->nullable()->default(null);

            $table->unique(["country", "province", "city"], 'weather_locations_country_province_city_unique');
            $table->nullableTimestamps();
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
