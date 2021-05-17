<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CleanUpCreatives.php
 */

namespace Neo\Console\Chores;

use Illuminate\Console\Command;
use Neo\BroadSign\Jobs\Creatives\DisableBroadSignCreative;
use Neo\BroadSign\Models\Creative as BSCreative;
use Neo\Models\Creative;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class CleanUpCreatives extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chores:creatives-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up ad-copies from BroadSign. Removing ad-copies with no match in BroadSign';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $allCreatives = BSCreative::all();

        $activeCreativesWithNoMatch = $allCreatives
            ->filter(fn($creative) => $creative->creation_user_id === 415059000)
            ->filter(fn($c) => !Creative::query()->where("broadsign_ad_copy_id", "=", $c->id)->exists() && $c->active);

        foreach ($activeCreativesWithNoMatch as $creative) {
            DisableBroadSignCreative::dispatchSync($creative->id);
        }

        return 0;
    }

    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
