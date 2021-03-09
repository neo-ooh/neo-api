<?php

namespace Neo\Console\Utils;

use Illuminate\Console\Command;
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
    protected $signature = 'utils:move-formats-resources 
    {to : Id of the receiving format} 
    {from* : Id.s of the old formats} 
    {--delete : Delete the old formats on completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move all resources from one or multiple format.s to another one.';

    /**
     * Run the migrations.
     */
    public function handle(): int {
        // Our goal is to move all resources in the old format.s to the new one.
        $oldFormatsIds = $this->argument("from");
        $newFormatId   = $this->argument("to");
        /** @var Format $newFormat */
        $newFormat = Format::find($newFormatId);
        /** @var FormatLayout $newLayout */
        $newLayout = $newFormat->layouts()->first();

        /** @var Frame $newFrame */
        $newFrame = $newLayout->frames()->first();

        // First, move all contents and their creatives to the new format.
        $layouts  = FormatLayout::query()->whereIn("format_id", $oldFormatsIds)->get();
        $contents = Content::query()->whereIn("layout_id", $layouts->pluck("id"))->get();

        $this->info("Contents:" . $contents->count());

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

        $this->info("Campaigns:" . $campaigns->count());

        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $campaign->format_id = $newFormatId;
            $campaign->save();
        }

        if($this->hasOption("delete")) {
            foreach ($oldFormatsIds as $formatId) {
                Format::query()->find($formatId)->delete();
            }
        }

        return 0;
    }
}
