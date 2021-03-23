<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RetargetAllCreatives.php
 */

namespace Neo\Console\Hotfixes;

use Illuminate\Console\Command;
use Neo\BroadSign\Jobs\Creatives\TargetCreative;
use Neo\BroadSign\Models\ResourceCriteria;
use Neo\Models\Creative;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class RetargetAllCreatives extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotfix:2021-02-16.2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retarget all ad-copies';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        // We start by loading all campaigns on connect who have a counterpart on BroadSign
        $creatives = Creative::query()->whereNotNull('broadsign_ad_copy_id')->get();

        $progressBar = $this->makeProgressBar(count($creatives));
        $progressBar->start();

        // Now for each campaign, we load its creatives, remove them, and trigger a re-targeting
        /** @var Creative $creative */
        foreach ($creatives as $creative) {
            $progressBar->advance();
            $progressBar->setMessage("Creative #({$creative->broadsign_ad_copy_id}) {$creative->id}");

            $criteria = ResourceCriteria::for($creative->broadsign_ad_copy_id);

            /** @var ResourceCriteria $criterion */
            foreach ($criteria as $criterion) {
                $criterion->active = false;
                $criterion->save();
            }

            TargetCreative::dispatchSync($creative->id);

        }

        $progressBar->setMessage("Hotfix done");
        $progressBar->finish();

        return 0;
    }

    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
