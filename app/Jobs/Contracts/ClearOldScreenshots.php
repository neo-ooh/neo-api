<?php

namespace Neo\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Neo\Models\ContractBurst;
use Neo\Models\ContractScreenshot;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ClearOldScreenshots extends Command {
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
        $screenshots = ContractScreenshot::query()
                                         ->whereDate("created_at", "<", Carbon::now()->subMonths(3))
                                         ->where("is_locked", "=", "0")
                                         ->get();

        $progressBar = $this->makeProgressBar($screenshots->count());

        /** @var ContractScreenshot $screenshot */
        foreach ($screenshots as $screenshot) {
            $screenshot->delete();
            $progressBar->advance();
        }

        $progressBar->finish();

        // Delete finished bursts without any screenshots
        ContractBurst::query()->whereDate("start_at", "<", Carbon::now()->subMonths(3))
                     ->whereDoesntHave("screenshots")->delete();

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
