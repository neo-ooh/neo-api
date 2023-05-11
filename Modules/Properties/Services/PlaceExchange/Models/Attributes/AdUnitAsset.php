<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitAsset.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;

class AdUnitAsset extends Data {
    /**
     * @param string                  $aspect_ratio
     * @param AdUnitAssetCapabilities $capability
     * @param string                  $category
     * @param string                  $image_url
     * @param string[]                $mimes
     * @param string                  $name
     * @param string                  $screen_count
     * @param int                     $size
     * @param string                  $type
     */
    public function __construct(
        public string                  $aspect_ratio,
        public AdUnitAssetCapabilities $capability,
        public int|null                $category_id,
        public string                  $image_url,
        public array                   $mimes,
        public string                  $name,
        public string                  $screen_count,
        public float                   $size,
        public string                  $type,
    ) {
    }
}
