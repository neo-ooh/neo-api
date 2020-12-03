<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\EditorTemplate;

class CreateEditorTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create("editor_templates", function(Blueprint $table) {
            $table->id();
            $table->string("slug", 16)->nullable(false)->unique();
            $table->json("parameters")->nullable(false);
            $table->timestamps();
        });

        $editorTemplate = new EditorTemplate([
            "slug" => "simple",
            "parameters" => ["title" => "string"]]);
        $editorTemplate->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::drop("editor_templates");
    }
}
