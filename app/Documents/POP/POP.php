<?php

namespace Neo\Documents\POP;

use Neo\Documents\Document;

class POP extends Document {
    protected array $contract;

    public function __construct() {
        parent::__construct([
            "margin_bottom" => 25,
            "packTableData" => true,
            "use_kwt"       => true,
        ]);
    }

    protected function ingest($data): bool {
        // TODO: Implement ingest() method.
    }

    public function build(): bool {
        // TODO: Implement build() method.
    }
}
