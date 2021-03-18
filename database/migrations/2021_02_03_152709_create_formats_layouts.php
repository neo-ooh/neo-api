<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Content;
use Neo\Models\Format;

class CreateFormatsLayouts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The layouts takes the form of an intermediary table between the formats and the frames
        // A format can have multiple layouts, and a layout can have multiple frames.
        Schema::create('formats_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('format_id')->index()->constrained('formats')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 64)->default("");
            $table->boolean("is_fullscreen")->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::dropColumns("formats", ["is_fullscreen"]);

        // Create a layout for each and every format who have frames
        $formats = Format::query()->has("frames")->with('frames')->get();

        foreach($formats as $format) {
            FormatLayout::create([
                "format_id" => $format->id,
                "name" => "Main",
            ]);
        }

        $formats = Format::query()->has("frames")->without("layouts")->with('frames')->get();

        Schema::table("frames", function (Blueprint $table) {
            $table->foreignId("layout_id")->after('id')->index()->nullable()->constrained("formats_layouts")->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Point all frames to the appropriate layout
        foreach($formats as $format) {
            $layout = $format->layouts()->first();
            foreach($format->frames as $frame) {
                $frame->layout_id = $layout->id;
                $frame->save();
            }
        }

        // Update the frames table to replace the format_id column by the layout_id
        Schema::table("frames", function (Blueprint $table) {
            $table->dropConstrainedForeignId("format_id");
        });

        // Update the content table
        Schema::table("contents", function (Blueprint $table) {
            $table->foreignId("layout_id")->after("library_id")->index()->nullable()->constrained("formats_layouts")->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Update all contents to reflect the change
        $contents = Content::all();

        foreach ($contents as $content) {
            if (count($content->format->layouts) === 0) {
                continue;
            }

            $content->layout_id = $content->format->layouts[0]->id;
            $content->save();
        }

        // Remove the format column from the contents
        Schema::table("contents", function(Blueprint $table) {
            $table->dropConstrainedForeignId("format_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formats_layouts');
    }
}
