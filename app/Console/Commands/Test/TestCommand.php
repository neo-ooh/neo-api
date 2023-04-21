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
use Neo\Jobs\Properties\UpdateDemographicFieldsJob;
use Neo\Modules\Properties\Exceptions\Synchronization\UnsupportedInventoryFunctionalityException;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    /**
     * @return void
     * @throws UnsupportedInventoryFunctionalityException
     * @throws InvalidInventoryAdapterException
     */
    public function handle() {
//        (new PullFullInventoryJob(1, debug: true))->handle();
//        $file = BroadSignAudienceFile::make(Location::query()->firstWhere("external_id", "=", 387294643));
//        $file->build();

        (new UpdateDemographicFieldsJob(3567))->handle();
    }
}
