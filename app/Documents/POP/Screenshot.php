<?php

namespace Neo\Documents\POP;

use Carbon\Carbon;
use Neo\Models\ContractScreenshot;

class Screenshot {
    public string $city;
    public string $province;

    public string $format;
    public Carbon $created_at;

    public string $dataURI;

    public function __construct(ContractScreenshot $screenshot) {
        $this->city       = $screenshot->burst->location->city;
        $this->province   = $screenshot->burst->location->province;
        $this->format     = $screenshot->burst->location->display_type->name;
        $this->created_at = $screenshot->created_at->tz("America/Toronto");


        $this->dataURI = 'data: image/jpeg;base64,' . base64_encode(file_get_contents($screenshot->url));

        // Mark the screenshot as locked
        $screenshot->is_locked = true;
        $screenshot->save();
    }
}
