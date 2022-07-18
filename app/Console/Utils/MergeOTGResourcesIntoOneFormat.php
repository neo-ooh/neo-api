<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MergeOTGResourcesIntoOneFormat.php
 */

namespace Neo\Console\Utils;

use Illuminate\Console\Command;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\FormatLayout;

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
        /** @var \Neo\Modules\Broadcast\Models\Format $newFormat */
        $newFormat = Format::query()->find($newFormatId);
        /** @var \Neo\Modules\Broadcast\Models\FormatLayout $newLayout */
        $newLayout = $newFormat->layouts()->first();

        /** @var \Neo\Modules\Broadcast\Models\Frame $newFrame */
        $newFrame = $newLayout->frames()->first();

        // First, move all contents and their creatives to the new format.
        $layouts  = FormatLayout::query()->whereIn("format_id", $oldFormatsIds)->get();
        $contents = Content::query()->whereIn("layout_id", $layouts->pluck("id"))->get();

        $this->info("Contents:" . $contents->count());

        /** @var \Neo\Modules\Broadcast\Models\Content $content */
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

        if ($this->hasOption("delete")) {
            foreach ($oldFormatsIds as $formatId) {
                Format::query()->find($formatId)->delete();
            }
        }

        return 0;
    }
}
