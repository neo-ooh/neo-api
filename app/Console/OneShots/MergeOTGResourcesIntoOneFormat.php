<?php

namespace Neo\Console\OneShots;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Neo\Models\Campaign;
use Neo\Models\Content;
use Neo\Models\Creative;
use Neo\Models\Format;
use Neo\Models\FormatLayout;
use Neo\Models\Frame;

class MergeOTGResourcesIntoOneFormat extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-shot:2021-03-08';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge all resources from the OTG formats to a unique one';

    /**
     * Run the migrations.
     */
    public function handle(): int {
        // Our goal is to merge all resources in the Depanneurs and gas stations formats into a single one.
        // For easier run, the formats IDs are hardcoded here. The value set here are for the production env.
        $oldFormatsIds = [4, 17];
        $newFormatId   = 43;
        /** @var Format $newFormat */
        $newFormat = Format::find($newFormatId);
        /** @var FormatLayout $newLayout */
        $newLayout = $newFormat->layouts()->first();
        $this->info($newLayout);

        /** @var Frame $newFrame */
        $newFrame = $newLayout->frames()->first();
        $this->info($newFrame);

        // First, move all contents and their creatives to the new format. thankfully, the old and new formats only have one frame.
        $layouts  = FormatLayout::query()->whereIn("format_id", $oldFormatsIds)->get();
        $contents = Content::query()->whereIn("layout_id", $layouts->pluck("id"));


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

        // Secondly, move all the campaigns to the new format
        $campaigns = Campaign::query()->whereIn("format_id", $oldFormatsIds)->get();

        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $campaign->format_id = $newFormatId;
            $campaign->save();
        }

        return 0;
    }
}
