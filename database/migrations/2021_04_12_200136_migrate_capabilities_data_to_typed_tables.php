<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Creative;
use Neo\Models\StaticCreative;

class MigrateCapabilitiesDataToTypedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // At this point, we only have static creatives. The migration process is therefore the same for all creatives, and consist of simply creating the required settings table
        Creative::withTrashed()->get()->each(function (Creative $creative) {
            StaticCreative::create([
                "creative_id" => $creative->id,
                "extension" => $creative->extension,
                "checksum" => $creative->checksum
            ]);
        });

        Schema::dropColumns("creatives", ["extension", "checksum"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Creative::query()->forceDelete();
    }
}
