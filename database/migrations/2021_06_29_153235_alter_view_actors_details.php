<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function dropView() {
        return <<<SQL
    DROP VIEW IF EXISTS `actors_details`;
SQL;
    }

    protected function createView() {
        return file_get_contents(database_path("views/2021_06_29_actors_details.sql"));
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement($this->dropView());
        DB::statement($this->createView());
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
