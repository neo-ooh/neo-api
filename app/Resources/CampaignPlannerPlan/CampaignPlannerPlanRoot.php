<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerPlanRoot.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Spatie\LaravelData\Data;

class CampaignPlannerPlanRoot extends Data {
    public function __construct(
        public array                        $layers,
        public array                        $flights,
        public array                        $compiled_selections,
        public array                        $compiled_flights,
        public CampaignPlannerPlanOdoo|null $odoo,
        public array                        $settings,
        public array                        $columns,
    ) {

    }
}
