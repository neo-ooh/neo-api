<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\BroadSign\BroadSign;
use Neo\Models\BroadSignCriteria;
use Neo\Models\Frame;

class AlterFramesTableAddCriteriaId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("frames", function (Blueprint $table) {
            $table->foreignId("criteria_id")->after("type")->constrained("broadsign_criteria")->cascadeOnUpdate()->restrictOnDelete();
        });

        // We need to fill the `criteria_id` column before removing the `type` column.
        // We start by making sure the appropriate criteria are present in the BroadSign_Criteria column
        $frames = Frame::all();

        // Since we use hardcoded values here as the app is already being used, we want to make sure we don't introduce unwanted values on new setup
        if($frames->count() > 0) {
            $advertisingId = BroadSignCriteria::query()->firstOrCreate([
                "broadsign_criteria_id" => 1143561,
            ], [
                "name" => "Advertising | Main",
            ])->id;

            $leftFrameId = BroadSignCriteria::query()->firstOrCreate([
                "broadsign_criteria_id" => 66246320,
            ], [
                "name" => "Left | Gauche",
            ])->id;

            $rightFrameId = BroadSignCriteria::query()->firstOrCreate([
                "broadsign_criteria_id" => 66246386,
            ], [
                "name" => "Right | Droite",
            ])->id;

            /** @var Frame $frame */
            foreach ($frames as $frame) {
                switch ($frame->type) {
                    case "MAIN":
                        $frame->criteria_id = $advertisingId;
                        break;
                    case "LEFT":
                        $frame->criteria_id = $leftFrameId;
                        break;
                    case "RIGHT":
                        $frame->criteria_id = $rightFrameId;
                        break;
                }
            }
        }

        // Now we can safely remove the frame `type` column
        Schema::dropColumns("frames", ["type"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns("frames", ["criteria_id"]);
    }
}
