<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Neo\Models\ActorLogo;

class AlterActorsLogoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop foreign ID
        Schema::table('actors_logo', function (Blueprint $table) {
           $table->dropForeign("actors_logo_id_foreign");
        });

        // Move column id to `actor_id`
        Schema::table('actors_logo', function (Blueprint $table) {
            $table->renameColumn("id", "actor_id");
            $table->dropIndex("PRIMARY");

            $table->foreign("actor_id")->references("id")->on("actors")->cascadeOnUpdate()->cascadeOnDelete();
            $table->index("actor_id");
        });

        // Add an id column
        Schema::table('actors_logo', function (Blueprint $table) {
            $table->id()->first();
        });

        // Rename files
        ActorLogo::all()->each(fn($logo) => Storage::move("actors_logo/{$logo->actor_id}.png", $logo->file_path));
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
}
