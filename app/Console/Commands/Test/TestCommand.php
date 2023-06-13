<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Reach\Models\Screen;
use Neo\Modules\Properties\Services\Reach\ReachAdapter;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     * @return void
     * @throws Exception
     */
    public function handle() {
        $file        = new Xlsx();
        $spreadsheet = $file->load(storage_path("app/reach-missing.xlsx"));

        $provider = InventoryProvider::query()->find(8);
        /** @var ReachAdapter $inventory */
        $inventory = $provider->getAdapter();

        foreach ($spreadsheet->getActiveSheet()->toArray() as $line) {
            $screenId = explode(":", $line[0])[0];
            $this->line($screenId);
            $screen = Screen::find($inventory->getConfig()->getClient(), $screenId);

            if (!$screen->name) {
                $this->comment("Already deleted");
                continue;
            }

            $screen->delete();
            $this->comment("Removed!");
        }
    }
}
