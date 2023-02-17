<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
use Illuminate\Support\Facades\DB;
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

        DB::query()
          ->from("demographic_values")
          ->where("property_id", "=", $this->propertyId)
          ->whereIn("value_id", $entries->pluck("id"))
          ->delete();

        // Register variables
        DemographicVariable::query()->insertOrIgnore($variables->toArray());

        // Insert values
        $values = $entries->map(fn($entry) => ([
            "property_id"     => $this->propertyId,
            "value_id"        => $entry["id"],
            "value"           => $entry["value"],
            "reference_value" => $entry["reference_value"],
            "created_at"      => $now,
            "updated_at"      => $now,
        ]));
        DemographicValue::query()->insertOrIgnore($values->toArray());

        $this->cleanUp();
    }

    public function failed() {
        $this->cleanUp();
    }

    protected function cleanUp() {
        unlink($this->filePath);
    }
}
