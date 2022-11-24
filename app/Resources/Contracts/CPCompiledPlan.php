<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledPlan.php
 */

namespace Neo\Resources\Contracts;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class CPCompiledPlan extends Data {
    public function __construct(
        public string         $version,

        #[DataCollectionOf(CPCompiledFlight::class)]
        public DataCollection $flights,
//        public array       $flights,

        public string|null    $contract,
        public string|null    $save_uid,
        public int|null       $owner_id,
        public string         $compiled_at,
    ) {
    }
}
