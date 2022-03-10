<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_05_17_181225_alter_frames_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Frame;
use Neo\Models\FrameSettingsBroadSign;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $frames = Frame::all();

        foreach ($frames as $frame) {
            $broadSignSetting = new FrameSettingsBroadSign();
            $broadSignSetting->frame_id = $frame->id;
            $broadSignSetting->criteria_id = $frame->criteria_id;
            $broadSignSetting->save();
        }

        Schema::table("frames", function (Blueprint $table) {
            $table->dropConstrainedForeignId("criteria_id");
        });
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
