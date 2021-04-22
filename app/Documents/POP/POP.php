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
        $this->contract = $data;

        return true;
    }

    public function build(): bool {
        $this->mpdf->WriteHTML("<pre>{print_r($this->contract, true)}</pre>");

        return true;
    }
}
