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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Neo\Documents\ProgrammaticExport\ProgrammaticExport;
use Neo\Models\Property;

class TestCommand extends Command {
    protected $signature = 'test:test';

    protected $description = 'Internal tests';

    public function handle() {
        /** @var Collection<Property> $otgProperties */
        $otgProperties = Property::query()
                                 ->where("network_id", "=", 3)
                                 ->get();

        $doc = ProgrammaticExport::make($otgProperties->pluck("actor_id")->toArray());
        $doc->build();
        $doc->output(Storage::disk("local")->path("otg-export.xlsx"));
    }
}
