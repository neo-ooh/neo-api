<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteOldScreenshots.php
 */

namespace Neo\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Neo\Models\Screenshot;
use Neo\Models\ScreenshotRequest;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class DeleteOldScreenshots extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:clear-screenshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove old contracts screenshots from the system";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        // Get all screenshots older than 60 days and not locked
        $screenshots = Screenshot::query()
                                 ->whereDate("received_at", "<", Carbon::now()->subMonths(3))
                                 ->whereDoesntHave("contracts")
                                 ->get();

        $progressBar = $this->makeProgressBar($screenshots->count());

        /** @var Screenshot $screenshot */
        foreach ($screenshots as $screenshot) {
            $screenshot->delete();
            $progressBar->advance();
        }

        $progressBar->finish();

        // Delete requests without any screenshots
        ScreenshotRequest::query()
                         ->whereDate("start_at", "<", Carbon::now()->subMonths(3))
                         ->whereDoesntHave("screenshots")
                         ->delete();

        return 0;
    }

    /**
     * @param int $steps
     *
     * @return ProgressBar
     */
    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');

        return $bar;
    }
}
