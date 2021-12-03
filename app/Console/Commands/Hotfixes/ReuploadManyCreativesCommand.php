<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReuploadManyCreativesCommand.php
 */

namespace Neo\Console\Commands\Hotfixes;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Neo\Models\Schedule;

class ReuploadManyCreativesCommand extends Command {
    protected $signature = 'hotfix:2021-12-03';

    public function handle() {
        $contentIds = Schedule::query()
                              ->whereDate("created_at", ">", Carbon::parse("2021-11-10"))
                              ->get("content_id")
                              ->pluck("content_id");

        foreach ($contentIds as $contentId) {
            $this->runCommand("content:re-upload", ["content" => $contentId], $this->getOutput());
        }
    }
}
