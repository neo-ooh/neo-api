<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitLocation.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class AdUnitLocation extends Data {
    public function __construct(
        public float|null|Optional $altitude,
        public float|null|Optional $bearing,
        public string|Optional     $city,
        public string|Optional     $country,
        public string|Optional     $dma,
        public int|null|Optional   $dma_code,
        public string              $horizontal_accuracy,
        public float               $lat,
        public string|Optional     $method,
        public float               $lon,
        public string|Optional     $region,
        public float|null|Optional $vertical_accuracy,
        public float|null|Optional $speed,
        public string|Optional     $zip,
    ) {
    }
}
