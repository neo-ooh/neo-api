<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IngestDemographicFileJob.php
 */

namespace Neo\Jobs\Demographics;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Neo\Jobs\Demographics\FilesParsers\EnvironicsDefaultParser;
use Neo\Jobs\Demographics\FilesParsers\EnvironicsPrizmParser;
use Neo\Models\DemographicValue;
use Neo\Models\DemographicVariable;

class IngestDemographicFileJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId, protected string $filePath, protected string $format) {
    }

    public function handle() {
        // Start by parsing the file based on its given format
        $parser = match ($this->format) {
            "environics-default" => new EnvironicsDefaultParser($this->filePath),
            "environics-prizm"   => new EnvironicsPrizmParser($this->filePath),
            default              => null,
        };

        if (!$parser) {
            $this->fail();
        }

        // Get all entries in the field
        $entries = collect($parser->getEntries());

        // Make sure each variable is referenced in the database
        $now       = Date::now("UTC");
        $variables = $entries->map(fn($entry) => ([
            "id"         => $entry["id"],
            "label"      => $entry["label"],
            "provider"   => "environics",
            "created_at" => $now,
            "updated_at" => $now,
        ]));

        DemographicVariable::query()->insertOrIgnore($variables->toArray());

        foreach ($entries as $entry) {
            DemographicValue::query()->updateOrInsert([
                "property_id" => $this->propertyId,
                "value_id"    => $entry["id"],
            ], [
                "value"           => $entry["value"],
                "reference_value" => $entry["reference_value"],
                "updated_at"      => Date::now("UTC"),
            ]);
        }

        $this->cleanUp();
    }

    public function failed() {
        $this->cleanUp();
    }

    protected function cleanUp() {
        unlink($this->filePath);
    }
}
