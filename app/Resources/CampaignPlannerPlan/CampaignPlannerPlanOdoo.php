<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerPlanOdoo.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Spatie\LaravelData\Data;

class CampaignPlannerPlanOdoo extends Data {
    public function __construct(
        public string     $contract,
        /**
         * @var array{int, string}
         */
        public array      $salespersonName,

        /**
         * Client
         *
         * @var array{int, string}
         */
        public array      $partnerName,

        /**
         * Advertiser
         *
         * @var array{int, string}
         */
        public null|array $analyticAccountName,

        public string     $date,
    ) {
    }
}
