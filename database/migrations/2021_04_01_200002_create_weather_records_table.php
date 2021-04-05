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
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('endpoint', 3);
            $table->string('country', 2);
            $table->string('province', 2);
            $table->string('city', 30);
            $table->string('locale', 5);
            $table->text('content');

            $table->unique(["endpoint", "country", "province", "city", "locale"], 'records_endpoint_country_province_city_locale_unique');
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
