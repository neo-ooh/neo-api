<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenPublisher.php
 */

namespace Neo\Modules\Properties\Services\Reach\Models\Attributes;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;

class ScreenPublisher extends Data {
    public function __construct(
        public int                     $id,
        public string|Optional         $name,
        public ScreenCurrency|Optional $currency,
        #[DataCollectionOf(ScreenCurrency::class)]
        public DataCollection|Optional $additional_currencies,
        public bool|Optional           $is_multi_currency_enabled,
        public bool|Optional           $is_hivestack_bidder,
        public bool|Optional           $is_vistar_bidder,
    ) {
    }
}
