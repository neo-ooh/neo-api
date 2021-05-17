<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeFormats.php
 */

namespace Neo\Services\Broadcast\BroadSign\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\DisplayType;
use Neo\Services\Broadcast\BroadSign\Models\Format as BSFormat;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job synchronizes the formats in the Network DB with those in the BroadSign APIs.
 * New Formats are added, existing ones are updated, and missing formats are removed.
 * Formats coming from BroadSign do NOT include any frame. It is up to the users in charge of formats to add the
 * proper screens using the web interface.
 *
 * @package Neo\Jobs
 */
class SynchronizeFormats extends BroadSignJob {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void {
        $broadsignFormats = BSFormat::all($this->getAPIClient());

        $progressBar = $this->makeProgressBar(count($broadsignFormats));
        $progressBar->start();

        foreach ($broadsignFormats as $bsformat) {
            $progressBar->setMessage("$bsformat->name ($bsformat->id)");

            DisplayType::query()->updateOrCreate([
                "external_id" => $bsformat->id,
            ], [
                "name" => $bsformat->name,
            ]);

            $progressBar->advance();
        }

        $progressBar->setMessage('Formats syncing done!');
        $progressBar->finish();
        (new ConsoleOutput())->writeln("");
    }
}
