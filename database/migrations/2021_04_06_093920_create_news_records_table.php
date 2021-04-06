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
            $table->engine = 'MyISAM';
            $table->increments('id');
            $table->string('cp_id', 64);
            $table->unsignedInteger('subject');
            $table->string('locale', 5);
            $table->string('headline');
            $table->dateTime('date');
            $table->string('media')->nullable();
            $table->integer('media_width')->nullable()->default(null);
            $table->integer('media_height')->nullable()->default(null);

            $table->index(["subject"], 'fk_record_subject');

            $table->unique(["cp_id", "subject"], 'news_records_cp_id_subject_unique');
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
