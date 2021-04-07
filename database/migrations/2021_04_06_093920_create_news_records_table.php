<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsRecordsTable extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'news_records';

    /**
     * Run the migrations.
     * @table news_records
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('cp_id', 64);
            $table->string('subject', 32);
            $table->string('locale', 5);
            $table->string('headline');
            $table->timestamp("date");
            $table->string('media')->nullable();
            $table->unsignedInteger('media_width')->nullable()->default(null);
            $table->unsignedInteger('media_height')->nullable()->default(null);
            $table->timestamps();

            $table->index(["subject"]);
            $table->unique(["cp_id", "subject"]);
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
