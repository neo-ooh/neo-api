<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeProperties.php
 */

namespace Neo\Jobs\Odoo;

use Illuminate\Bus\Queueable;
use Illuminate\Console\Command;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Neo\Models\Odoo\Property;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class SynchronizeProperties extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'properties:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Synchronizes properties with Odoo";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $client = OdooConfig::fromConfig()->getClient();

        $properties = Property::all("property_id");


        $progressBar = $this->makeProgressBar($properties->count());
        $progressBar->start();
        $progressBar->setMessage("Syncing...");

        foreach($properties as $property) {
            $progressBar->setMessage("Syncing property #" . $property->property_id . ": ");

            SyncPropertyDataJob::dispatchSync($property->property_id, $client);

            $progressBar->advance();
        }

        $progressBar->finish();

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
