<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Campaign;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Format;
use Neo\Models\FormatLayout;
use Neo\Models\Frame;

class MergeOtgFormatsIntoOne extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Our goal is to merge all resources in the Depanneurs and gas stations formats into a single one.
        // For easier run, the formats IDs are hardcoded here. The value set here are for the production env.
        $oldFormatsIds = [4, 17];
        $newFormatId = 43;
        /** @var Format $newFormat */
        $newFormat = Format::find($newFormatId);
        /** @var FormatLayout $newLayout */
        $newLayout = $newFormat->layouts()->first();

        /** @var Frame $newFrame */
        $newFrame = $newLayout->frames()->first();

        // First, move all contents and their creatives to the new format. thankfully, the old and new formats only have one frame.
        $layouts = FormatLayout::query()->whereIn("format_id", $oldFormatsIds)->get();
        $contents = Content::query()->whereIn("layout_id", $layouts);

        DB::beginTransaction();

        /** @var Content $content */
        foreach ($contents as $content) {
            /** @var Creative $creative */
            $content->layout_id = $newLayout->id;
            $content->save();

            foreach ($content->creatives as $creative) {
                $creative->frame_id = $newFrame->id;
                $creative->save();
            }
        }

        DB::commit();

        // Secondly, move all the campaigns to the new format
        $campaigns = Campaign::query()->whereIn("format_id", $oldFormatsIds)->get();

        DB::beginTransaction();

        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $campaign->format_id = $newFormatId;
            $campaign->save();
        }

        DB::commit();
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
