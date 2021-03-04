<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\DisplayType;
use Neo\Models\Format;

class AlterFormats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Start by creating the new tables
        Schema::create("display_types", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("broadsign_display_type_id");
            $table->string("name", 64);
            $table->timestamps();
        });

        Schema::create("formats_display_types", function (Blueprint $table) {
            $table->foreignId("format_id")->references("id")->on("formats")->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId("display_type_id")->references("id")->on("display_types")->cascadeOnDelete()->cascadeOnUpdate();
        });

        // Populate the display types using the formats and link each new display type to its format
        $formats = Format::all();
        foreach ($formats as $format) {
            $dt = new DisplayType([
                "broadsign_display_type_id" => $format->broadsign_display_type,
                "name" => $format->slug,
            ]);
            $dt->save();

            $dt->formats()->attach($format->id);
        }

        // Remove now unnecessary columns from the formats table
        Schema::dropColumns("formats", ["broadsign_display_type", "slug"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("display_types");
        Schema::drop("formats_display_types");
    }
}
