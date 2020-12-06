<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - SynchronizeFormats.php
 */

namespace Neo\BroadSign\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\BroadSign\Models\Format as BSFormat;
use Neo\Models\Format;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronizes the formats in the Network DB with those in the BroadSign APIs.
 * New Formats are added, existing ones are updated, and missing formats are removed.
 * Formats coming from BroadSign do NOT include any frame. It is up to the users in charge of formats to add the
 * proper screens using the web interface.
 *
 * @package Neo\Jobs
 */
class SynchronizeFormats implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void {
        $broadsignFormats = BSFormat::all();

        $progressBar = $this->makeProgressBar(count($broadsignFormats));
        $progressBar->start();

        foreach ($broadsignFormats as $bsformat) {
            $progressBar->setMessage("{$bsformat->name} ({$bsformat->id})");

            Format::query()->firstOrCreate([
                "broadsign_display_type" => $bsformat->id,
            ],
                [
                    "slug"       => $this->supportMapping[$bsformat->name] ?? $bsformat->name,
                    "name"       => $bsformat->name,
                    "is_enabled" => true,
                ]);

            $progressBar->advance();
        }

        $progressBar->setMessage('Formats syncing done!');
        $progressBar->finish();
        (new ConsoleOutput())->writeln("");
    }

    /**
     * @param int $steps
     *
     * @return ProgressBar
     */
    protected function makeProgressBar(int $steps): ProgressBar {
        $bar = new ProgressBar(new ConsoleOutput(), $steps);
        $bar->setFormat('%current%/%max% [%bar%] %message%');
        $bar->setMessage('Fetching data...');

        return $bar;
    }
}
